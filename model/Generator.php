<?php

/**
 * @link http://helgusoft.pl/
 * @copyright Copyright (c) 2015 helgusoft, Gdańsk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace grzegorzpierzakowski\hii\model;

use \yii\gii\CodeFile;
use Yii;

class Generator extends \yii\gii\generators\model\Generator
{
    /**
     * @var array key-value pairs for mapping tableNames to modelNames
     */
    public $tableModelMap = [];

    /**
     *  pairs of column => RelationName that will be generated when found 
     * if two tables have more than one relation
     */
    public $customRelations = [];

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Hii Model';
    }

    public function init()
    {
        parent::init();
        if (isset(Yii::$app->params['hii-model']))
            foreach (Yii::$app->params['hii-model'] as $key => $value)
                $this->$key = $value;
    }
    /** 
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['model.php', 'base/model.php'];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $relations = $this->generateRelations();
        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {

            $className = $this->generateClassName($tableName);

            $tableSchema = $db->getTableSchema($tableName);
            $params = [
                'tableName' => $tableName,
                'className' => $className,
                'tableSchema' => $tableSchema,
                'labels' => $this->generateLabels($tableSchema),
                'rules' => $this->generateRules($tableSchema),
                'relations' => isset($relations[$className]) ? $relations[$className] : [],
                'ns' => $this->ns,
            ];
            foreach (['', 'base/'] as $file)
                $files[] = $this->generateFile($className, $params, $file);
        }
        return $files;
    }

    /**
     * Generates a class name from the specified table name.
     *
     * @param string $tableName the table name (which may contain schema prefix)
     *
     * @return string the generated class name
     */
    protected function generateClassName($tableName, $useSchemaName = null)
    {
        return isset($this->tableModelMap[$tableName]) ? $this->takeNameFromArray($tableName) : $this->generate2ClassNames($tableName, $useSchemaName);
    }

    protected function generate2ClassNames($tableName, $useSchemaName = null)
    {
        $tableName = preg_replace('/2/','2_', $tableName);
        return parent::generateClassName($tableName, $useSchemaName);
    }
    protected function generateRelations()
    {
        $relations = $this->generateAdvancedRelations();
        foreach ($relations AS $model => $relInfo) {
            foreach ($relInfo AS $relName => $relData) {
                $relations[$model][$relName][0] = $this->injectNamespace($relations[$model][$relName][0]);
            }
        }
        return $relations;
    }

    private function injectNamespace($relation)
    {
        $relation = preg_replace('/(has[A-Za-z0-9]+\()([a-zA-Z0-9]+::)/', '$1__NS__$2', $relation);
        return str_replace('__NS__', "\\{$this->ns}\\", $relation);
    }

    private function takeNameFromArray($tableName)
    {
        Yii::trace("Converted '{$tableName}' from config->tableModelMap.", __METHOD__);
        return $this->tableModelMap[$tableName];
    }

    private function removePrefixes($tableName)
    {
        return ($regexp = $this->getDbConnection()->tablePrefix) ? preg_replace($regexp, '', $tableName) : $tableName;
    }

    private function generateFile($className, $params, $file)
    {
        return new CodeFile(
            Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . "/$file$className.php",
            $this->render($file . 'model.php', $params)
        );
    }
    
    protected function generateAdvancedRelations()
    {
        if (!$this->generateRelations) return [];
        $db = $this->getDbConnection();

        $schemaName = ($pos = strpos($this->tableName, '.') !== false) ? substr($this->tableName, 0, $pos) : $schemaName = '';

        $relations = [];
        foreach ($db->getSchema()->getTableSchemas($schemaName) as $table) {
            $tableName = $table->name;
            $className = $this->generateClassName($tableName);
            foreach ($table->foreignKeys as $refs) {
                $refTable = $refs[0];
                unset($refs[0]);
                $fks = array_keys($refs);
                $refClassName = $this->generateClassName($refTable);
                
                // Add relation for this table
                $link = $this->generateRelationLink(array_flip($refs));
                $relationName = $this->generateRelationName($relations, $table, $fks[0], false);
                $relationName .= array_key_exists($fks[0], $this->customRelations) ? $refClassName : '';
                if ($className != $refClassName) $relations[$className][$relationName] = [
                    "return \$this->hasOne($refClassName::className(), $link);",
                    $refClassName,
                    false,
                ];

                // Add relation for the referenced table
                $hasMany = false;
                if (count($table->primaryKey) > count($fks)) {
                    $hasMany = true;
                } else {
                    foreach ($fks as $key) {
                        if (!in_array($key, $table->primaryKey, true)) {
                            $hasMany = true;
                            break;
                        }
                    }
                }
                $link = $this->generateRelationLink($refs);
                $relationName =  $this->generateRelationName($relations, $db->getTableSchema($refTable), $className, $hasMany);
                $relationName .= array_key_exists($fks[0], $this->customRelations) ? $this->customRelations[$fks[0]] : '';
                $relations[$refClassName][$relationName] = [
                    "return \$this->" . ($hasMany ? 'hasMany' : 'hasOne') . "($className::className(), $link);",
                    $className,
                    $hasMany,
                ];
            }

            if (($fks = $this->checkPivotTable($table)) === false) {
                continue;
            }
            $table0 = $fks[$table->primaryKey[0]][0];
            $table1 = $fks[$table->primaryKey[1]][0];
            $className0 = $this->generateClassName($table0);
            $className1 = $this->generateClassName($table1);

            $link = $this->generateRelationLink([$fks[$table->primaryKey[1]][1] => $table->primaryKey[1]]);
            $viaLink = $this->generateRelationLink([$table->primaryKey[0] => $fks[$table->primaryKey[0]][1]]);
            $relationName = $this->generateRelationName($relations, $db->getTableSchema($table0), $table->primaryKey[1], true);
            $relations[$className0][$relationName] = [
                "return \$this->hasMany($className1::className(), $link)->viaTable('" . $this->generateTableName($table->name) . "', $viaLink);",
                $className1,
                true,
            ];

            $link = $this->generateRelationLink([$fks[$table->primaryKey[0]][1] => $table->primaryKey[0]]);
            $viaLink = $this->generateRelationLink([$table->primaryKey[1] => $fks[$table->primaryKey[1]][1]]);
            $relationName = $this->generateRelationName($relations, $db->getTableSchema($table1), $table->primaryKey[0], true);
            $relations[$className1][$relationName] = [
                "return \$this->hasMany($className0::className(), $link)->viaTable('" . $this->generateTableName($table->name) . "', $viaLink);",
                $className0,
                true,
            ];
        }

        return $relations;
    }
}

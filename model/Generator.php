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
use \yii\helpers\Inflector;
use Yii;


class Generator extends \yii\gii\generators\model\Generator
{
    /**
     * @var array key-value pairs for mapping reltion patterns eg. 'column_name' => 'relationName'
     */
    public $customMap = [];

    /**
     * @var array key-value pairs for mapping tableNames to modelNames
     */
    public $tableModelMap = [];
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Hii Model';
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
        $files     = [];
        $relations = $this->generateRelations();
        $db        = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {

            $className = $this->generateClassName($tableName);

            $tableSchema = $db->getTableSchema($tableName);
            $params      = [
                'tableName'   => $tableName,
                'className'   => $className,
                'tableSchema' => $tableSchema,
                'labels'      => $this->generateLabels($tableSchema),
                'rules'       => $this->generateRules($tableSchema),
                'relations'   => isset($relations[$className]) ? $relations[$className] : [],
                'ns'          => $this->ns,
            ];

            foreach(['', 'base/'] as $file)
                    $files[] = $this->generateFile($className, $params, $file);
        }
        return $files;
    }

    private function generateFile($className, $params, $file)
    {
        return new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . "/$file$className.php",
                $this->render($file . 'model.php', $params)
            );
    }
    /**
     * Generates a class name from the specified table name.
     *
     * @param string $tableName the table name (which may contain schema prefix)
     *
     * @return string the generated class name
     */
    protected function generateClassName($tableName)
    {
        return isset($this->tableModelMap[$tableName]) ? $this->takeNameFromArray($tableName) : $this->calculateClassName($tableName);
    }
    
    protected function generateRelations()
    {
        $relations = parent::generateRelations();

        // inject namespace
        $ns = "\\{$this->ns}\\";
        foreach ($relations AS $model => $relInfo) {
            foreach ($relInfo AS $relName => $relData) {

                $relations[$model][$relName][0] = preg_replace(
                    '/(has[A-Za-z0-9]+\()([a-zA-Z0-9]+::)/',
                    '$1__NS__$2',
                    $relations[$model][$relName][0]
                );
                $relations[$model][$relName][0] = str_replace('__NS__', $ns, $relations[$model][$relName][0]);
            }
        }
        return $relations;
    }

    private function takeNameFromArray($tablename)
    {
            Yii::trace("Converted '{$tableName}' from config->tableModelMap.", __METHOD__);
            return $this->tableModelMap[$tableName];
    }
    
    private function calculateClassName($tableName)
    {
        $className = Inflector::id2camel($this->removePrefixes($tableName), '_');
        Yii::trace("Converted '{$tableName}' to '{$className}'.", __METHOD__);
        return $className;
    }
    
    private function removePrefixes($tableName)
    {
        return ($regexp = $this->getDbConnection()->tablePrefix) ? preg_replace($regexp, '', $tableName) : $tableName;
    }
}

<?php

class CQuestion extends ObjectModel
{
    /** @var int $id_cquestion - the ID of Department */
    public $id_cquestion;

    /** @var String $question - Question */
    public $question;

    /** @var String $answer - Answer */
    public $answer;

    /** @var Bool $is_published - Question publish status */
    public $is_published;

    /** @var array $categories - categories IDs for department */
    public $categories;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cquestion',
        'primary' => 'id_cquestion',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'id_cquestion' =>  array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'),
            'question' =>    array('type' => self::TYPE_STRING),
            'answer' =>   array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'is_published' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        )
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        $this->categories = array();
        if ($this->id_cquestion){
            $this->categories = self::getCategoriesByFaq($this->id_cquestion);
        }
    }

    public function save($null_values = false, $auto_date = true)
    {
        $result = parent::save($null_values, $auto_date);
        $db_categories = self::getCategoriesByFaq($this->id_cquestion);
        $remove_categories = array_diff($db_categories, $this->categories);
        $new_categories = array_diff($this->categories, $db_categories);
        if ($result && count($new_categories)) {
            foreach ($new_categories as $category_id)
                $result &= Db::getInstance()->insert('category_cquestion', array('id_category' => $category_id, 'id_cquestion' => $this->id));
        }
        if ($result && count($remove_categories)) {
            $delete_sql = 'DELETE FROM '._DB_PREFIX_.'category_cquestion WHERE id_category IN ('.implode(',', $remove_categories).') AND id_cquestion = '. $this->id;
            $result &= Db::getInstance()->execute($delete_sql);
        }
        return (bool)$result;
    }

    public function delete()
    {
        $result = parent::delete();
        $result &= Db::getInstance()->delete('category_cquestion', 'id_cquestion = '.$this->id);
        return (bool)$result;
    }

    private static function getCategoriesByFaq($id_cquestion)
    {
        if (is_null($id_cquestion))
        {
            return array();
        }
        $query = new DbQuery();
        $query->select('ccq.`id_category`');
        $query->from('category_cquestion', 'ccq');
        $query->where('ccq.`id_cquestion` = '.$id_cquestion);
        return array_column(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query), 'id_category');
    }

    public static function gets($id_category = null, $with_categories_detail = false, $nl2br = false)
    {
        if ($with_categories_detail){
            return self::getsWithCategoriesDetail($id_category, $nl2br);
        }
        $query = new DbQuery();
        $query->select('cq.`id_cquestion`, cq.`question`, cq.`answer`, cq.`is_published`');
        $query->from('cquestion', 'cq');
        if (!is_null($id_category)) {
            $query->leftJoin('category_cquestion', 'ccq', 'cq.`id_cquestion` = ccq.`id_cquestion`');
            $query->where('ccq.`id_category` = '.$id_category);
        }
        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($nl2br) {
            foreach ($rows as &$row) {
                $row['answer'] = nl2br($row['answer']);
            }
        }
        return $rows;
    }

    private static function getsWithCategoriesDetail($id_category = null, $nl2br = false){
        $id_lang = Context::getContext()->language->id;
        $sql = 'SELECT cq.`id_cquestion`, cq.`question`, cq.`answer`, cq.`is_published`, cn.`categories_names`, cn.`categories_ids`
                FROM cquestion cq
                LEFT JOIN
                    (SELECT cq.`id_cquestion`, GROUP_CONCAT(DISTINCT (cl.`name`) SEPARATOR \'; \') AS `categories_names`, GROUP_CONCAT(DISTINCT (cl.`id_category`) SEPARATOR \';\') AS `categories_ids`
                    FROM `cquestion` cq
                    LEFT JOIN `category_cquestion` ccq ON cq.`id_cquestion` = ccq.`id_cquestion`
                    LEFT JOIN `category` c ON ccq.`id_category` = c.`id_category`
                    LEFT JOIN `category_lang` cl ON c.`id_category` = cl.`id_category` AND cl.id_lang = '.$id_lang.'
                    GROUP BY cq.`id_cquestion`) cn ON cq.id_cquestion = cn.id_cquestion
                WHERE 1'.((!is_null($id_category)) ? "ccq.id_category = $id_category" : '');
        $rows = Db::getInstance()->executeS($sql);
        if ($nl2br) {
            foreach ($rows as &$row) {
                $row['answer'] = nl2br($row['answer']);
            }
        }
        return $rows;
    }

    public static function getAllCategories(){
        $id_lang = Context::getContext()->language->id;
        $query = new DbQuery();
        $query->select('ccq.`id_category`, cl.`name`');
        $query->from('category_cquestion', 'ccq');
        $query->leftJoin('category_lang', 'cl', 'ccq.`id_category` = cl.`id_category` AND cl.`id_lang` = '.$id_lang);
        $query->groupBy('ccq.`id_category`');
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }
}
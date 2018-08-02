<?php

if (!defined('_PS_VERSION_'))
{
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once _PS_MODULE_DIR_.'ps_categoryfaq/classes/CQuestion.php';

class Ps_CategoryFaq extends Module implements WidgetInterface
{
    private $_html = '';
    private $templateFile;

    public function __construct()
    {
        $this->name = 'ps_categoryfaq';
        $this->tab = 'others';
        $this->version = '0.0.1';
        $this->author = 'Vitaly Kondratiev';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Category FAQ');
        $this->description = $this->l('This module add Q&A block to category pages');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('CQuestion'))
            $this->warning = $this->l('No name provided');
        $this->templateFile = 'module:ps_categoryfaq/views/templates/hook/ps_categoryfaq.tpl';
    }

    public function installDb()
    {
        $sql = array();
        $return = true;
        include(dirname(__FILE__).'/sql_install.php');
        foreach ($sql as $s) {
            $return &= Db::getInstance()->execute($s);
        }
        return $return;
    }

    public function uninstallDb()
    {
        $sql = array();
        include(dirname(__FILE__).'/sql_install.php');
        foreach ($sql as $name => $v) {
            Db::getInstance()->execute('DROP TABLE '.$name);
        }
        return true;
    }

    public function installTab(){
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminCategoryFaq';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Category FAQ');
        }
        $tab->id_parent =  (int)Tab::getIdFromClassName('AdminParentThemes');
        $tab->module = $this->name;
        return $tab->add();
    }

    public function installHooks(){
        return $this->registerHook('displayCategoryQuestion') && $this->registerHook('moduleRoutes');
    }

    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminCategoryFaq');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        } else {
            return false;
        }
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->installDb() ||
            !$this->installTab() ||
            !$this->installHooks()
        )
            return false;
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !$this->uninstallDb() ||
            !$this->uninstallTab()
        )
            return false;
        return true;
    }

    public function getContent()
    {
        $this->_html = null;

        $this->submitHandler();

        $this->_html .= $this->renderAddForm();
        $this->_html .= $this->renderList();

        return $this->_html;
    }

    public function submitHandler()
    {
        if (Tools::isSubmit('savecquestion'))
        {
            $id_cquestion = Tools::getValue('id_cquestion');
            $question = Tools::getValue('question');
            $answer = Tools::getValue('answer');
            $is_published = Tools::getValue('is_published');
            $categories = Tools::getValue('categories_tree', array());
            if (!$question || empty($question) || !Validate::isGenericName($question) ||
                !$answer || empty($answer) || !Validate::isCleanHtml($answer)) {
                $this->_html .= $this->displayError($this->l('Invalid value'));
                return;
            }
            if ($id_cquestion)
                $cquestion = new CQuestion($id_cquestion);
            else{
                $cquestion = new CQuestion();
            }
            $cquestion->question = $question;
            $cquestion->answer = $answer;
            $cquestion->is_published = $is_published;
            $cquestion->categories = $categories;
            $cquestion->save();
        }
        elseif (Tools::isSubmit('deletecquestion'))
        {
            $id_cquestion = Tools::getValue('id_cquestion', 0);
            $cquestion = new CQuestion($id_cquestion);
            $deleted = $cquestion->delete();
        }
        elseif (Tools::isSubmit('publishedcquestion'))
        {
            $id_cquestion = Tools::getValue('id_cquestion', 0);
            $cquestion = new CQuestion($id_cquestion);
            $cquestion->is_published = (int)!$cquestion->is_published;
            $cquestion->save();
        }
    }

    public function renderAddForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $is_update_form = (Tools::getIsset('updatecquestion') && !Tools::getValue('updatecquestion'));
        $fields_values = $this->getFieldsValues($is_update_form);
        $root_category = Category::getRootCategory();
        $root_category_id = $root_category->id_category;
        $categories = new HelperTreeCategories('associated-categories-tree');
        $categories
            ->setUseSearch(true)
            ->setUseCheckBox(true)
            ->setSelectedCategories(isset($fields_values['categories_tree']) ? $fields_values['categories_tree'] : array())
            ->setRootCategory($root_category_id)
            ->setInputName('categories_tree');
        $categories_tree = $categories->render();

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => ($is_update_form) ?
                    $this->l('Update category question') : $this->l('Add a new question'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Question'),
                    'name' => 'question',
                    'required' => true
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Answer'),
                    'name' => 'answer',
                    'required' => true
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Published'),
                    'name' => 'is_published',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Admin.Global')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Admin.Global')
                        )
                    ),
                ),
                array(
                    'type'  => 'categories_select',
                    'label' => $this->l('Question categories'),
                    'desc'  => $this->l('Categories for this question.'),
                    'name'  => 'categories_tree',
                    'category_tree'  => $categories_tree
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_cquestion',
                    'required' => true,
                    'value' => 0,
                ),
            ),
            'submit' => array(
                'name' => 'savecquestion',
                'title' => ($is_update_form) ? $this->l('Update') : $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );
        $helper->fields_value = $fields_values;

        return $helper->generateForm($fields_form);
    }

    public function getFieldsValues($update_form){
        $id_cquestion = false;
        $question = '';
        $answer = '';
        $is_published = 1;
        $categories_tree = array();

        if ($update_form) {
            $cquestion = new CQuestion(Tools::getValue('id_cquestion'));
            $id_cquestion = $cquestion->id_cquestion;
            $question = $cquestion->question;
            $answer = $cquestion->answer;
            $is_published = $cquestion->is_published;
            $categories_tree = $cquestion->categories;
        }

        if ($this->error) {
            $id_cquestion = Tools::getValue('id_cquestion', $id_cquestion);
            $question = Tools::getValue('question', $question);
            $answer = Tools::getValue('answer', $answer);
            $is_published = Tools::getValue('is_published', $is_published);
            $categories_tree = Tools::getValue('categories_tree', $categories_tree);
        }

        $fields_values = array(
            'id_cquestion' => $id_cquestion,
            'question' => $question,
            'answer' => $answer,
            'is_published' => $is_published,
            'categories_tree' => $categories_tree,
        );

        return $fields_values;
    }

    public function renderList(){
        $fields_list = array(
            'question' => array(
                'title' => $this->l('Question'),
                'type' => 'text',
                'orderby' => true,
            ),
            'answer' => array(
                'title' => $this->l('Answer'),
                'type' => 'text',
                'orderby' => false,
            ),
            'categories_names' => array(
                'title' => $this->l('Categories'),
                'type' => 'text',
                'orderby' => false,
            ),
            'is_published' => array(
                'title' => $this->l('Published'),
                'type' => 'bool',
                'align' => 'center',
                'active' => 'published',
                'class' => 'fixed-width-xs',
                'orderby' => false,
            )
        );
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_cquestion';
        $helper->table = 'cquestion';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->title = $this->l('Questions list');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $cquestions = CQuestion::gets(null, true);
        return $helper->generateList($cquestions, $fields_list);
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        $id_category = $configuration['category']['id'];
        if (!$this->isCached($this->templateFile, $this->getCacheId("categoryfaq-$id_category"))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }
        return $this->fetch($this->templateFile, $this->getCacheId("categoryfaq-$id_category"));
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $id_category = $configuration['category']['id'];
        $category_name = $configuration['category']['name'];
        $question_block_header = $this->l('Category %s FAQ');
        $cquestions = CQuestion::gets($id_category, false, true);
        return array(
            'question_block_header' => $this->trans($question_block_header, array("\"$category_name\"")),
            'questions' => $cquestions,
        );
    }

    public function hookModuleRoutes() {
        return array(
            'module-ps_categoryfaq-questions' => array(
                'controller' => 'questions',
                'rule' => 'categories-questions',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ps_categoryfaq',
                )
            ),
        );
    }
}
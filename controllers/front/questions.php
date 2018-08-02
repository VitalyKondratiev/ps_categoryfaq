<?php

class Ps_CategoryFaqQuestionsModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();
        $cquestions = CQuestion::gets(null, true, true);
        $all_categories_caption = $this->l('All categories');
        $categories = array_merge(
            array(
                array(
                    'id_category' => 0,
                    'name' => $all_categories_caption,
                )
            ),
            CQuestion::getAllCategories());
        $this->context->smarty->assign(array(
            'caption' => $this->l('Questions list'),
            'questions' => $cquestions,
            'empty_text' => $this->l('No questions'),
            'categories_block' => array(
                'caption' => $this->l('Categories'),
                'categories' => $categories,
            )
        ));
        if ((int)Tools::getValue('content_only') != 1){
            $this->setTemplate('module:ps_categoryfaq/views/templates/front/questions.tpl');
        }
        else {
            $this->setTemplate('module:ps_categoryfaq/views/templates/front/questions_content_only.tpl');
        }
    }

    public function setMedia()
    {
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/questions.js');
        return parent::setMedia();
    }
}
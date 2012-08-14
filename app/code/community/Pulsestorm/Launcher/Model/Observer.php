<?php
/*
Copyright (c) 2012 Pulse Storm LLC

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class Pulsestorm_Launcher_Model_Observer
{
    public function addNav($observer)
    {
        $controller         = $observer->getAction();
        if($this->_shouldBail($controller))
        {
            return;
        }
        
        $this->_addExtraFrontendFiles($controller);

        $json = $this->_renderDefaultNavigationJson($controller);

        $this->_addMainJavascript($controller,$json);
        

    }    
    
    public function addConfigNav($observer)
    {
        $controller         = $observer->getAction();
        if($this->_shouldBail($controller))
        {
            return;
        }
    
        $launcher_links = Mage::getSingleton('pulsestorm_launcher/links');
        $block = $controller->getLayout()->createBlock('adminhtml/system_config_tabs')->initTabs();
        foreach($block->getTabs() as $tab)
        {
            foreach($tab->getSections() as $section)
            {
                $label = 'System Configuration - ' . $section->getLabel();
                $code = $section->getId();
                $url   = $url = Mage::getModel('adminhtml/url');
                $url   = $url->getUrl('adminhtml/system_config/edit', array('_current'=>true, 'section'=>$code));
                $launcher_links->addLink($label,$url);
            }
        }    
    }
    
    public function addHookJavascript($observer)
    {
        $controller         = $observer->getAction();
        if($this->_shouldBail($controller))
        {
            return;
        }
    
        $layout             = $controller->getLayout();
        $before_body_end    = $layout->getBlock('before_body_end');
        
        $block = $layout->createBlock('adminhtml/template')
        ->setTemplate('pulsestorm_launcher/hook.phtml')
        ->setLinks(Mage::getSingleton('pulsestorm_launcher/links')->getLinks());
        $before_body_end->append($block);
        
    }
    
    protected function _renderDefaultNavigationJson($controller)
    {
        $layout             = $controller->getLayout();
        $block              = $layout->createBlock('pulsestorm_launcher/page_menu');
        $menu               = $block->getMenuArray();
        $json               = Mage::helper('core')->jsonEncode($menu);    
        $json               = $block->secretKeyJsonStringReplace($json);
        return $json;
    }
    
    protected function _addMainJavascript($controller, $json)
    {
        $layout             = $controller->getLayout();
        $before_body_end    = $layout->getBlock('before_body_end');
        
        $first = Mage::getStoreConfig('pulsestorm_launcher/options/shortcut_code_first');
        $second  = Mage::getStoreConfig('pulsestorm_launcher/options/shortcut_code_second');
        
        $code = '17_32';        //default to ctrl-space
        if(is_numeric($first) && is_numeric($second))
        {
            $code = $first . '_' . $second;
        }
        
        $block              = $layout->createBlock('adminhtml/template')
        ->setTemplate('pulsestorm_launcher/js-nav.phtml')
        ->setJson($json)
        ->setCombinedCodes('17_77');
        $before_body_end->append($block);    
    }
    
    protected function _addExtraFrontendFiles($controller)
    {
        $layout             = $controller->getLayout();
        
        $head               = $layout->getBlock('head');
        $head->addCss('pulsestorm_launcher/main.css')
        ->addItem('js_css', 'prototype/windows/themes/default.css')
        ->addCss('lib/prototype/windows/themes/magento.css');    
    }
    
    protected function _shouldBail($controller)
    {
        return strpos($controller->getFullActionName(), 'adminhtml_') !== 0;
    }
    
}
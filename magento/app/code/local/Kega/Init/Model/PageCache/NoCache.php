<?php

/**
 *
 */
class Kega_Init_Model_PageCache_NoCache extends Enterprise_PageCache_Model_Container_Abstract
{
    const CACHE_TAG_PREFIX = 'nocache';

    /**
     * Get identifier from customer cookie
     *
     * @return string
     */
    protected function _getIdentifier()
    {
        return false;
    }

    /**
     * @param void
     * @return string
     */
    protected function _getCacheId()
    {
        return 'SYSTEM_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    /**
     * Render block content
     *
     * @param void
     * @return string
     */
    protected function _renderBlock()
    {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $block;
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());

        return $block->toHtml();
    }


    /**
     * Normally this method would save the block content to the cache.
     * In this case we do nothing and just return $this because we don't want the block content to be cached.
     *
     * @param string $blockContent
     * @return Enterprise_PageCache_Model_Container_Abstract
     */
    public function saveCache($blockContent)
    {
        return $this;
    }
}

<?php
class Core_View_Helper_Navigation_Menu extends Zend_View_Helper_Navigation_Menu
{

    /**
     * Renders a normal menu (called from {@link renderMenu()})
     *
     * @param  Zend_Navigation_Container $container         container to render
     * @param  string                    $ulClass           CSS class for first UL
     * @param  string                    $indent            initial indentation
     * @param  int|null                  $minDepth          minimum depth
     * @param  int|null                  $maxDepth          maximum depth
     * @param  bool                      $onlyActive        render only active branch?
     * @param  bool                      $expandSibs        render siblings of active
     *                                                      branch nodes?
     * @param  string|null               $ulId              unique identifier (id) for
     *                                                      first UL
     * @param  bool                      $addPageClassToLi  adds CSS class from
     *                                                      page to li element
     *
     * @return string
     */
    protected function _renderMenu(Zend_Navigation_Container $container, $ulClass, $indent, $minDepth, $maxDepth, $onlyActive, $expandSibs, $ulId, $addPageClassToLi)
    {
        $html = '';

        // find deepest active
        if($found = $this->findActive($container, $minDepth, $maxDepth))
        {
            $foundPage  = $found['page'];
            $foundDepth = $found['depth'];
        }
        else
        {
            $foundPage = null;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);
        if(is_int($maxDepth))
        {
            $iterator->setMaxDepth($maxDepth);
        }

        // iterate container
        $prevDepth = -1;
        foreach($iterator as $page)
        {
            $depth    = $iterator->getDepth();
            $isActive = $page->isActive(true);
            if($depth < $minDepth || !$this->accept($page))
            {
                // page is below minDepth or not accepted by acl/visibilty
                continue;
            }
            else if($expandSibs && $depth > $minDepth)
            {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if($foundPage)
                {
                    if($foundPage->hasPage($page))
                    {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    }
                    else if($page
                        ->getParent()
                        ->isActive(true)
                    )
                    {
                        // page is a sibling of the active branch...
                        $accept = true;
                    }
                }
                if(!$isActive && !$accept)
                {
                    continue;
                }
            }
            else if($onlyActive && !$isActive)
            {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if($foundPage)
                {
                    if($foundPage->hasPage($page))
                    {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    }
                    else if($foundPage
                        ->getParent()
                        ->hasPage($page)
                    )
                    {
                        // page is a sibling of the active page...
                        if(!$foundPage->hasPages() || is_int($maxDepth) && $foundDepth + 1 > $maxDepth)
                        {
                            // accept if active page has no children, or the
                            // children are too deep to be rendered
                            $accept = true;
                        }
                    }
                }

                if(!$accept)
                {
                    continue;
                }
            }

            // make sure indentation is correct
            $depth -= $minDepth;
            $myIndent = $indent . str_repeat('        ', $depth);

            if($depth > $prevDepth)
            {
                $attribs = array();

                // start new ul tag
                if(0 == $depth)
                {
                    $attribs = array(
                        'class' => $ulClass,
                        'id'    => $ulId,
                    );
                }
                else
                {
                    $attribs = array(
                        'class' => 'dropdown-menu'
                    );
                }

                // We don't need a prefix for the menu ID (backup)
                $skipValue = $this->_skipPrefixForId;
                $this->skipPrefixForId();
                $html .= $myIndent . '<ul' . $this->_htmlAttribs($attribs) . '>' . self::EOL;

                // Reset prefix for IDs
                $this->_skipPrefixForId = $skipValue;
            }
            else if($prevDepth > $depth)
            {
                // close li/ul tags until we're at current depth
                for($i = $prevDepth; $i > $depth; $i--)
                {
                    $ind = $indent . str_repeat('        ', $i);
                    $html .= $ind . '    </li>' . self::EOL;
                    $html .= $ind . '</ul>' . self::EOL;
                }
                // close previous li tag
                $html .= $myIndent . '    </li>' . self::EOL;
            }
            else
            {
                // close previous li tag
                $html .= $myIndent . '    </li>' . self::EOL;
            }

            // render li tag and page
            $liClass = '';
            if($isActive && $addPageClassToLi)
            {
                $liClass = $this->_htmlAttribs(
                    array('class' => 'active ' . $page->getClass())
                );
            }
            else if($isActive)
            {
                $liClass = $this->_htmlAttribs(array('class' => 'active'));
            }
            else if($addPageClassToLi)
            {
                $liClass = $this->_htmlAttribs(
                    array('class' => $page->getClass())
                );
            }
            $html .= $myIndent . '    <li' . $liClass . '>' . self::EOL . $myIndent . '        ' . $this->htmlify(
                $page
            ) . self::EOL;

            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if($html)
        {
            // done iterating container; close open ul/li tags
            for($i = $prevDepth + 1; $i > 0; $i--)
            {
                $myIndent = $indent . str_repeat('        ', $i - 1);
                $html .= $myIndent . '    </li>' . self::EOL . $myIndent . '</ul>' . self::EOL;
            }
            $html = rtrim($html, self::EOL);
        }

        return $html;
    }
}
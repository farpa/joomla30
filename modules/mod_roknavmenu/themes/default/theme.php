<?php
/**
 * @version   $Id: theme.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
class RokNavMenuDefaultTheme extends AbstractRokMenuTheme {

    protected $defaults = array(
    );

    public function getFormatter($args){
        require_once(dirname(__FILE__) . '/formatter.php');
        return new RokNavMenuDefaultFormatter($args);
    }

    public function getLayout($args){
        require_once(dirname(__FILE__) . '/layout.php');
        return new RokMavMenuDefaultLayout($args);
    }
}

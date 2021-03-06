<?php

/**
 * This file contains the "SecurityComponentAdmin" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2018 <silverstripedev@catalyst.net.nz>
 * @copyright 2018 Catalyst.Net Ltd
 * @license https://www.catalyst.net.nz (Commercial)
 * @link https://www.catalyst.net.nz
 */

namespace NZTA\SDLT\ModelAdmin;

use NZTA\SDLT\Model\SecurityComponent;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldViewButton;

/**
 * Class SecurityComponentAdmin
 *
 */
class SecurityComponentAdmin extends ModelAdmin
{
    /**
     * @var string[]
     */
    private static $managed_models = [
        SecurityComponent::class,
    ];

    /**
     * @var string
     */
    private static $url_segment = 'security-components-admin';

    /**
     * @var string
     */
    private static $menu_title = 'Security Components';
}

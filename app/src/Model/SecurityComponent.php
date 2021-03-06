<?php

/**
 * This file contains the "SecurityComponent" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2018 <silverstripedev@catalyst.net.nz>
 * @copyright 2018 Catalyst.Net Ltd
 * @license https://www.catalyst.net.nz (Commercial)
 * @link https://www.catalyst.net.nz
 */

namespace NZTA\SDLT\Model;

use SilverStripe\GraphQL\Scaffolding\Interfaces\ScaffoldingProvider;
use SilverStripe\GraphQL\Scaffolding\Scaffolders\SchemaScaffolder;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\LiteralField;

/**
 * Class SecurityComponent
 *
 * @property string Name
 * @property string Description
 * @method HasManyList Controls()
 */
class SecurityComponent extends DataObject implements ScaffoldingProvider
{
    /**
     * @var string
     */
    private static $table_name = 'SecurityComponent';

    /**
     * @var array
     */
    private static $db = [
        'Name' => 'Varchar(255)',
        'Description' => 'Text',
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'Controls' => SecurityControl::class,
    ];

    /**
     * Belongs_many_many relationship
     * @var array
     */
    private static $has_many = [
        'SelectedComponent' => SelectedComponent::class,
    ];

    /**
     * @var array
     */
    private static $belongs_many_many = [
        'DefaultsForCVATask' => Task::class
    ];

    /**
     * @param SchemaScaffolder $scaffolder The scaffolder
     * @return SchemaScaffolder
     */
    public function provideGraphQLScaffolding(SchemaScaffolder $scaffolder)
    {
        $typeScaffolder = $scaffolder
            ->type(self::class)
            ->addFields([
                'ID',
                'Name',
                'Description',
            ]);

        // Provide relations
        $typeScaffolder
            ->nestedQuery('Controls')
            ->setUsePagination(false)
            ->end();

        $typeScaffolder
            ->operation(SchemaScaffolder::READ)
            ->setName('readSecurityComponents')
            ->setUsePagination(false)
            ->end();

        return $typeScaffolder;
    }

    /**
     * Allow logged-in user to access the model
     *
     * @param Member|null $member The member
     * @return bool
     */
    public function canView($member = null)
    {
        return (Security::getCurrentUser() !== null);
    }

    /**
     * Generate the body of a ticket in e.g. JIRA, to be pushed to its REST API.
     *
     * @see {@link IssueTrackerTicket} and {@link IssueTrackerSystem}.
     * @return IssueTrackerTicket
     */
    public function getTicket()
    {
        $issue = $this->issueTrackerService->issue();
        $list = [];

        foreach ($this->Controls() as $control) {
            /** Manually inject the {@link Control}'s ID for later reconciliation */
            $title = sprintf('%s (#%d)', $control->Name, $control->ID);
            $list[] = [$title, $control->Description];
        }

        $issue->setListItems($list, true);

        return $issue;
    }

    /**
     * Will be exercised when value is null.
     *
     * @return string
     */
    public function getName() : string
    {
        return (string) $this->getField('Name');
    }

    /**
     * Will be exercised when value is null.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return (string) $this->getField('Description');
    }

    /**
     * get cms fields
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $instructions = LiteralField::create(
            'JIRAControlsChecklistMessage',
            sprintf(
                "<div class='warning message'>%s</div>",
                'Each of these controls is a line on a checklist generated on'
                .' the JIRA ticket. They will all be combined and shown as'
                .' "unchecked" with the Title followed by the description when'
                .' submitted to JIRA.'
            )
        );
        $name = TextField::create('Name')
            ->setDescription('This is the title of the component. It is'
            .' displayed on the component selection screen.');
        $description = TextareaField::create('Description')
            ->setDescription('This contains the instructions that appear inside'
            .' the panel at the top of the JIRA story.');

        $fields->addFieldsToTab('Root.Main', [$name, $description]);
        $fields->addFieldToTab('Root.Controls', $instructions, 'Controls');

        $fields->removeByName(['SelectedComponent', 'DefaultsForCVATask']);

        return $fields;
    }
}

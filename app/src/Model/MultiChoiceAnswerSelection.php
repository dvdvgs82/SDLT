<?php

/**
 * This file contains the "MultiChoiceAnswerSelection" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2019 <silverstripedev@catalyst.net.nz>
 * @copyright 2019 Catalyst.Net Ltd
 * @license https://www.catalyst.net.nz (Commercial)
 * @link https://www.catalyst.net.nz
 */

namespace NZTA\SDLT\Model;

use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use NZTA\SDLT\Model\Risk;
use NZTA\SDLT\Model\AnswerInputField;

/**
 * An instance of MultiChoiceAnswerSelection is related to {@link AnswerInputField}'s on
 * one side and {@link Risk} objects on the other.
 *
 * Instances encapsulate A single choice in a multi-choice question. In addition,
 * for each such answer that may appear as a checkbox or radio button, admins
 * can relate >=1 {@link Risk} record, and for each such combination, a weighting
 * value also. These are used to generate risk scores for Risk-Type Questionnaires,
 * Tasks and Pillars.
 */
class MultiChoiceAnswerSelection extends DataObject
{
    /**
     * @var array
     */
    private static $db = [
        'Label' => DBVarchar::class,
        'Value' => DBVarchar::class,
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'Risks' => Risk::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'AnswerInputField' => AnswerInputField::class,
    ];

    /**
     * @var array
     */
    private static $many_many_extraFields = [
        'Risks' => [
            'Weight' => DBInt::class,
        ]
    ];

    /**
     * @var string
     */
    private static $table_name = 'AnswerInputBlock';

    /**
     * @var string
     */
    private static $singular_name = 'Answer Selection';

    /**
     * @var string
     */
    private static $plural_name = 'Answer Selections';

    /**
     * @var array
     */
    private static $summary_fields = [
        'Label' => 'Selection Label',
        'Value' => 'Selection Value',
        'Risks.Count' => 'No. Risks & Weights',
    ];

    /**
     * Is the {@link Questionnaire} to which this record's {@link AnswerInputField}
     * and {@link Question} relations are related, a "Risk" type?
     *
     * @return boolean
     */
    public function isRiskType(): bool
    {
        if (!$this->exists()) {
            return false;
        }

        $questionnaireIsRiskType = $this->AnswerInputField()
                ->Question()
                ->Questionnaire()
                ->isRiskType();

        $taskIsRiskType = $this->AnswerInputField()
                ->Question()
                ->Task()
                ->isRiskType();

        return $questionnaireIsRiskType || $taskIsRiskType;
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Remove default scaffolded fields
        $fields->removeByName([
            'Risks',
            'AnswerInputFieldID',
            'AnswerActionFieldID'
        ]);
        $fields->dataFieldByName('Label')
                ->setDescription('This is the label for a single checkbox or radio selection.');
        $fields->dataFieldByName('Value')
                ->setDescription('This is the value for a single checkbox or radio selection and
                    please enter a unique value for the current checkbox or radio field.');

        if ($this->isRiskType()) {
            // Allow inline-editing for the "Weight" value
            $componentEditableFields = (new GridFieldEditableColumns())
                    ->setDisplayFields(['Weight' => [
                    'title' => 'Weighting',
                    'field' => NumericField::create('ManyMany[Weight]')
            ]]);
            // No need for an edit button. The weight is the only editable field
            $config = GridFieldConfig_RelationEditor::create()
                    ->addComponent($componentEditableFields, GridFieldEditButton::class);

            $fields->addFieldToTab('Root.Main', GridField::create(
                            'Risks',
                            'Risk Associations',
                            $this->Risks(),
                            $config
                    )
            );
        }

        return $fields;
    }

    /**
     * @return ValidationResult
     */
    public function validate()
    {
        $result = parent::validate();

        if ($this->AnswerInputField()->isMultipleChoice() &&
            $selections = $this->AnswerInputField()->AnswerSelections()) {
            $values = $selections->exclude('ID', $this->ID)->Column('Value');

            if ($values && in_array($this->Value, $values)) {
                $result->addError(
                  sprintf('"%s" already exists, please add a unique value.', $this->Value)
                );
            }
        }

        return $result;
    }
}

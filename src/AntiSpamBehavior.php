<?php
/**
 * AntiSpamBehavior Class file
 *
 * @author    Chris Yates
 * @copyright Copyright &copy; 2020 BeastBytes - All Rights Reserved
 * @license   BSD 3-Clause
 * @package   BeastBytes\AntiSpam
 */

namespace BeastBytes\AntiSpam;

use yii\base\Behavior;
use yii\base\Model;
use yii\validators\RequiredValidator;

/**
 * AntiSpamBehavior provides methods that simplify using and validating the AntiSpamInput widget
 */
class AntiSpamBehavior extends Behavior
{
    /**
     * @var bool Whether the model has spam
     */
    public $hasSpam = false;
    /**
     * @var array|string The name(s) of attribute(s) using the {HashInput} widget. This can be a comma string in the case of a
     * single attribute that use the default hash attribute name, an array where an element may be a string for an
     * attribute that uses the default hash attribute name, or a name=>value pair where `name` is the attribute name and
     * `value` is the corresponding hash attribute name
     */
    public $hashAttributes;
    /**
     * @var array Value(s) of the {HashInput} widget fields as name=>value pairs where `name` is the hash attribute name
     * and `value` its value
     * @internal
     */
    public $hashValues = [];
    /**
     * @var array|string The name(s) of attribute(s) using the {HoneyPotInput} widget. This can be a string in the case
     * of a single attribute that use the default hash attribute name, an array where an element may be a string for an
     * attribute that uses the default honeypot attribute name, or a name=>value pair where `name` is the attribute name
     * and `value` is the corresponding honeypot attribute name
     */
    public $honeyPotAttributes;
    /**
     * @var array Value(s) of the {HoneyPotInput} widget fields as name=>value pairs where `name` is the honeyPot
     * attribute name and `value` its value
     * @internal
     */
    public $honeyPotValues = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->normaliseAttributes('hash');
        $this->normaliseAttributes('honeyPot');
    }


    /**
     * @inheritdoc
     */
    public function events(): array
    {
        return [
            Model::EVENT_AFTER_VALIDATE => function () {
                // Set the real attributes with their HoneyPot attribute values
                foreach ($this->honeyPotAttributes as $attribute => $honeyPotAttribute) {
                    $this->owner->$attribute = $this->owner->$honeyPotAttribute;
                }
            }
        ];
    }

    /**
     * Returns attribute labels including those for HoneyPot inputs
     *
     * Call this method and return its return value from the owner model's `attributeLabels()` method
     * ```php
     * public function attributeLabels()
     * {
     *   $attributeLabels = [
     *     'attribute1' => 'Label1',
     *     'attribute2' => 'Label2',
     *     // ...
     *     'attributeN' => 'LabelN'
     *   ];
     *   return $this->antiSpamAttributeLabels($attributeLabels);
     * }
     * ```
     *
     * @param array $attributeLabels the owner model's attribute labels
     * @return array attribute labels including those for HoneyPot inputs
     */
    public function antiSpamAttributeLabels(array $attributeLabels): array
    {
        $antiSpamAttributeLabels = [];

        foreach ($attributeLabels as $attribute => $label) {
            if (array_key_exists($attribute, $this->honeyPotAttributes)) {
                $antiSpamAttributeLabels[$this->honeyPotAttributes[$attribute]] = $label;
            }
        }

        return array_merge($attributeLabels, $antiSpamAttributeLabels);
    }

    /**
     * Returns validation rules including those for AntiSpam inputs
     *
     * Call this method and return its return value from the owner model's `rules()` method
     * ```php
     * public function rules()
     * {
     *   $rules = [
     *     ['rule1'],
     *     ['rule2'],
     *     // ...
     *     ['ruleN']
     *   ];
     *   return $this->antiSpamRules($rules);
     * }
     * ```
     *
     * @param array $rules the owner model's rules
     * @return array rules including those for AntiSpam inputs
     */
    public function antiSpamRules(array $rules): array
    {
        // disable client side validation for the real HoneyPot attributes;
        // they are validated on the server where the real attribute is expected to be empty
        // The real attribute gets the real value after validation
        foreach ($rules as &$rule) {
            $ruleAttributes = $rule[0];

            if (is_string($ruleAttributes)) {
                $ruleAttributes = [$ruleAttributes];
            }

            foreach ($this->honeyPotAttributes as $attribute => $honeyPotAttribute) {
                $i = array_search($attribute, $ruleAttributes);
                if ($i !== false) {
                    $ruleAttributes[$i] = $honeyPotAttribute;
                    $rule[0] = $ruleAttributes;

                    $newRule = array_slice($rule, 1, null, true);
                    if ($newRule[1] === 'required' || $newRule[1] === RequiredValidator::class) {
                        // remove the 'required' rule for the real attribute or it will always fail
                        continue;
                    }

                    $newRule += ['enableClientValidation' => false];
                    array_unshift($newRule, $attribute);
                    array_unshift($rules, $newRule);
                }
            }
        }

        // add new validation rules for honeyPot and hash attributes
        $rules[] = [array_keys($this->hashAttributes), 'ValidateHash'];
        $rules[] = [array_values($this->hashAttributes), 'safe'];
        $rules[] = [array_keys($this->honeyPotAttributes), 'ValidateHoneyPot'];

        return $rules;
    }

    /**
     * @param string $attribute The attribute for which to return the hash attribute name
     * @return string the hash attribute name
     */
    public function getHashAttribute(string $attribute): string
    {
        return $this->hashAttributes[$attribute];
    }

    /**
     * @param string $attribute The attribute for which to return the honeyPot attribute name
     * @return string the honeyPot attribute name
     */
    public function getHoneyPotAttribute(string $attribute): string
    {
        return $this->honeyPotAttributes[$attribute];
    }

    /**
     * Validates a Hash {AntiSpamInput} widget attribute and sets the hasSpam property if the attribute is invalid
     * @param string $attribute the name of the attribute to be validated
     * @see hasSpam
     */
    public function validateHash(string $attribute)
    {
        $hashAttribute = $this->getHashAttribute($attribute);
        if (md5($this->owner->$attribute) !== $this->owner->$hashAttribute) {
            $this->hasSpam = true;
        }
    }

    /**
     * Validates a Honey Pot {AntiSpamInput} widget attribute and sets the hasSpam property if the attribute is invalid
     * @param string $attribute the name of the attribute to be validated
     * @see hasSpam
     */
    public function validateHoneyPot(string $attribute)
    {
        if (strlen($this->owner->$attribute) > 0) {
            $this->hasSpam = true;
        }
    }

    private function normaliseAttributes(string $for)
    {
        $_attributes = [];
        $attributes = $for . 'Attributes';
        $values = $for . 'Values';

        if (is_string($this->$attributes)) {
            $this->$attributes = [$this->$attributes];
        }

        foreach ($this->$attributes as $k => $v) {
            if (is_int($k)) {
                $attribute = md5($v);
                $_attributes[$v] = $attribute;
                $this->$values[$attribute] = null;
            } else {
                $_attributes[$k] = $v;
                $this->$values[$v] = null;
            }
        }
        $this->$attributes = $_attributes;
    }
}

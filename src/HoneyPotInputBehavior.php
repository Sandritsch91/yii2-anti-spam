<?php
/**
 * HoneyPotInputBehavior Class file
 *
 * @author    Chris Yates
 * @copyright Copyright &copy; 2020 BeastBytes - All Rights Reserved
 * @license   BSD 3-Clause
 * @package   BeastBytes\AntiSpam
 */

namespace BeastBytes\AntiSpam;

use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;

/**
 */
class HoneyPotInputBehavior extends AntiSpamInputBehavior
{
    /**
	 * @inheritdoc
	 */
	public function generateInput()
	{
        $pos = strrpos($this->owner->options['id'], '-');
        $honeyPotId = substr($this->owner->options['id'], 0, $pos + 1) . $this->antiSpamAttribute;

        echo Html::beginTag('div', ['id' => $this->owner->id]);
        echo Html::activeTextInput($this->owner->model, $this->owner->attribute);
        echo Html::activeTextInput($this->owner->model, $this->antiSpamAttribute, array_merge(
            $this->owner->options, ['id' => $honeyPotId]
        ));
        echo Html::endTag('div');

        if ($this->owner->field->form->enableClientScript) {
            $clientOptions = $this->getClientOptions();
            if (!empty($clientOptions)) {
                $this->owner->field->form->attributes[] = $clientOptions;
            }
        }

        $this->registerClientScript();
	}

    /**
     * Registers the CSS to hide the real input JS to adjust the form group
     */
    private function registerClientScript()
    {
        $inputId = Html::getInputId($this->owner->model, $this->antiSpamAttribute);
        $class = [
            'form-group',
            "field-$inputId",
            $this->owner->field->form->requiredCssClass
        ];
        $class = join(' ', $class);

        $view = $this->owner->getView();
        $view->registerCss('#' . $this->owner->id . '{position:relative;}#' . $this->owner->id . ' input:first-child{border:none;bottom:0;height:0;position:absolute;right:0;width:0;z-index:-10;}');
        $view->registerJs("var {$this->owner->id}_as = document.getElementById('{$this->owner->id}').parentElement;{$this->owner->id}_as.className = '$class';{$this->
        owner->id}_as.getElementsByTagName('label').item(0).setAttribute('for', '$inputId');", View::POS_END);
    }

    /**
     * Returns the JS options for the field.
     * @return array the JS options.
     */
    protected function getClientOptions()
    {
        $attribute = Html::getAttributeName($this->antiSpamAttribute);
        if (!in_array($attribute, $this->owner->model->activeAttributes(), true)) {
            return [];
        }

        $clientValidation = $this->isClientValidationEnabled();
        $ajaxValidation = $this->isAjaxValidationEnabled();

        if ($clientValidation) {
            $validators = [];
            foreach ($this->owner->model->getActiveValidators($attribute) as $validator) {
                /* @var $validator \yii\validators\Validator */
                $js = $validator->clientValidateAttribute($this->owner->model, $attribute, $this->owner->field->form->getView());
                if ($validator->enableClientValidation && $js != '') {
                    if ($validator->whenClient !== null) {
                        $js = "if (({$validator->whenClient})(attribute, value)) { $js }";
                    }
                    $validators[] = $js;
                }
            }
        }

        if (!$ajaxValidation && (!$clientValidation || empty($validators))) {
            return [];
        }

        $options = [];

        $inputID = Html::getInputId($this->owner->model, $this->antiSpamAttribute);
        $options['id'] = $inputID;
        $options['name'] = $this->antiSpamAttribute;

        $options['container'] = isset($this->owner->field->selectors['container']) ? $this->owner->field->selectors['container'] : ".field-$inputID";
        $options['input'] = isset($this->owner->field->selectors['input']) ? $this->owner->field->selectors['input'] : "#$inputID";
        if (isset($this->owner->field->selectors['error'])) {
            $options['error'] = $this->owner->field->selectors['error'];
        } elseif (isset($this->owner->field->errorOptions['class'])) {
            $options['error'] = '.' . implode('.', preg_split('/\s+/', $this->owner->field->errorOptions['class'], -1, PREG_SPLIT_NO_EMPTY));
        } else {
            $options['error'] = isset($this->owner->field->errorOptions['tag']) ? $this->owner->field->errorOptions['tag'] : 'span';
        }

        $options['encodeError'] = !isset($this->owner->field->errorOptions['encode']) || $this->owner->field->errorOptions['encode'];
        if ($ajaxValidation) {
            $options['enableAjaxValidation'] = true;
        }
        foreach (['validateOnChange', 'validateOnBlur', 'validateOnType', 'validationDelay'] as $name) {
            $options[$name] = $this->owner->field->$name === null ? $this->owner->field->form->$name : $this->owner->field->$name;
        }

        if (!empty($validators)) {
            $options['validate'] = new JsExpression('function (attribute, value, messages, deferred, $form) {' . implode('', $validators) . '}');
        }

        if ($this->owner->field->addAriaAttributes === false) {
            $options['updateAriaInvalid'] = false;
        }

        // only get the options that are different from the default ones (set in yii.activeForm.js)
        return array_diff_assoc($options, [
            'validateOnChange' => true,
            'validateOnBlur' => true,
            'validateOnType' => false,
            'validationDelay' => 500,
            'encodeError' => true,
            'error' => '.help-block',
            'updateAriaInvalid' => true,
        ]);
    }

    /**
     * Checks if client validation enabled for the field.
     * @return bool
     */
    protected function isClientValidationEnabled()
    {
        return $this->owner->field->enableClientValidation ||
            $this->owner->field->enableClientValidation === null && $this->owner->field->form->enableClientValidation;
    }

    /**
     * Checks if ajax validation enabled for the field.
     * @return bool
     */
    protected function isAjaxValidationEnabled()
    {
        return $this->owner->field->enableAjaxValidation ||
            $this->owner->field->enableAjaxValidation === null && $this->owner->field->form->enableAjaxValidation;
    }
}

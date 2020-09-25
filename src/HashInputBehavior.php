<?php
/**
 * HashInputBehavior Class file
 *
 * @author    Chris Yates
 * @copyright Copyright &copy; 2020 BeastBytes - All Rights Reserved
 * @license   BSD 3-Clause
 * @package   BeastBytes\AntiSpam
 */

namespace BeastBytes\AntiSpam;

use yii\helpers\Html;

/**
 * Generates a HashInput field and registers the required JavaScript
 */
class HashInputBehavior extends AntiSpamInputBehavior
{
	/**
	 * @inheritdoc
	 */
	public function generateInput()
	{
		echo Html::activeTextInput($this->owner->model, $this->owner->attribute, array_merge(
            $this->owner->field->inputOptions, $this->owner->options
        ));
		echo Html::activeHiddenInput($this->owner->model, $this->antiSpamAttribute);
		$this->registerClientScript();
	}

	/**
	 * Registers client script
	 */
	private function registerClientScript()
	{
		HashAsset::register($this->owner->getView());

		$attributeId = Html::getInputId($this->owner->model, $this->owner->attribute);
		$hashAttributeId = Html::getInputId($this->owner->model, $this->antiSpamAttribute);

		$this->owner->getView()->registerJs("document.getElementById('$attributeId').onblur=function(){document.getElementById('$hashAttributeId').value=hex_md5(document.getElementById('$attributeId').value);};");
	}
}

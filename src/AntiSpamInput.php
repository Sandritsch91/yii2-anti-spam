<?php

namespace BeastBytes\AntiSpam;

use yii\widgets\InputWidget;

class AntiSpamInput extends InputWidget
{
    /**
     * @var HashInputBehavior|HoneyPotInputBehavior The attached behavior
     */
    private $_behavior;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        /** @var AntiSpamBehavior $behavior */
        foreach ($this->model->getBehaviors() as $behavior) {
            if (get_class($behavior) === 'BeastBytes\AntiSpam\AntiSpamBehavior') {
                break;
            }
        }

        if (in_array($this->attribute, array_keys($behavior->hashAttributes))) {
            $this->_behavior = $this->attachBehavior('HashInput', [
                'class' => HashInputBehavior::class,
                'antiSpamAttribute' => $behavior->hashAttributes[$this->attribute]
            ]);
        } elseif (in_array($this->attribute, array_keys($behavior->honeyPotAttributes))) {
            $this->_behavior = $this->attachBehavior('HoneyPotInput', [
                'class' => HoneyPotInputBehavior::class,
                'antiSpamAttribute' => $behavior->honeyPotAttributes[$this->attribute]
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->_behavior->generateInput();
    }
}

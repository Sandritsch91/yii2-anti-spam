<?php


namespace BeastBytes\AntiSpam;


class AntiSpamInput extends \yii\widgets\InputWidget
{
    /**
     * @var yii\base\Behavior The attached behavior
     */
    private $_behavior;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

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

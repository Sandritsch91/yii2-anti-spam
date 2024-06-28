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

use yii\base\InvalidCallException;
use yii\base\UnknownPropertyException;

/**
 * AntiSpamTrait provides getting and setting of AntiSpam attributes
 * @see AntiSpamBehavior
 * @see HashInput
 * @see HoneyPotInput
 */
trait AntiSpamTrait
{
    /**
     * Returns the value of a HashInput attribute, HoneyPotInput attribute, or an object property
     * @param string $name the name
     * @return mixed the value of a HashInput attribute, HoneyPotInput attribute, or an object property
     * @throws UnknownPropertyException if the property is not defined
     * @see __set()
     */
    public function __get($name)
    {
        foreach ($this->getBehaviors() as $behavior) {
            if (get_class($behavior) === 'BeastBytes\AntiSpam\AntiSpamBehavior') {
                break;
            }
        }

        if (in_array($name, array_keys($behavior->hashValues))) {
            return $behavior->hashValues[$name];
        } elseif (in_array($name, array_keys($behavior->honeyPotValues))) {
            return $behavior->honeyPotValues[$name];
        } else {
            return parent::__get($name);
        }
    }

    /**
     * Sets the value of a HashInput attribute, HoneyPotInput attribute, or an object property
     * @param string $name the name
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is write-only
     * @see __get()
     */
    public function __set($name, $value)
    {
        foreach ($this->getBehaviors() as $behavior) {
            if (get_class($behavior) === 'BeastBytes\AntiSpam\AntiSpamBehavior') {
                break;
            }
        }

        if (in_array($name, array_keys($behavior->hashValues))) {
            $behavior->hashValues[$name] = $value;
        } elseif (in_array($name, array_keys($behavior->honeyPotValues))) {
            $behavior->honeyPotValues[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }
}

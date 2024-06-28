<?php
/**
 * AntiSpamInputBehavior Class file
 *
 * @author    Chris Yates
 * @copyright Copyright &copy; 2020 BeastBytes - All Rights Reserved
 * @license   BSD 3-Clause
 * @package   BeastBytes\AntiSpam
 */

namespace BeastBytes\AntiSpam;

use yii\base\Behavior;

/**
 * Base class for AntiSpam input behaviors
 */
abstract class AntiSpamInputBehavior extends Behavior
{
    /**
     * @var string The anti-spam attribute name
     */
    public $antiSpamAttribute;

    /**
     * Generate the anti-spam input field
     * @return void
     */
    abstract public function generateInput(): void;
}

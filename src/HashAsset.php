<?php
/**
 * VerifyAsset Class file
 *
 * @author    Chris Yates
 * @copyright Copyright &copy; 2015 BeastBytes - All Rights Reserved
 * @license   BSD 3-Clause
 * @package   AntiSpam
 */

namespace BeastBytes\AntiSpam;

use yii\web\AssetBundle;

/**
 * Asset bundle for the \BeastBytes\AntiSpam\Verify JavaScript
 */
class HashAsset extends AssetBundle
{
    public $basePath = '@webroot';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->js = ['md5-min.js'];
        $this->sourcePath = __DIR__ . '/assets';
    }
}

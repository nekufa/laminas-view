<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Helper;

use Laminas\View\Helper;
use Laminas\View\Helper\Placeholder\Registry;

/**
 * Test class for Laminas_View_Helper_InlineScript.
 *
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTests
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 */
class InlineScriptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Laminas_View_Helper_InlineScript
     */
    public $helper;

    /**
     * @var string
     */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        Registry::unsetRegistry();
        $this->basePath = __DIR__ . '/_files/modules';
        $this->helper = new Helper\InlineScript();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->helper);
    }

    public function testNamespaceRegisteredInPlaceholderRegistryAfterInstantiation()
    {
        $registry = Registry::getRegistry();
        if ($registry->containerExists('Laminas_View_Helper_InlineScript')) {
            $registry->deleteContainer('Laminas_View_Helper_InlineScript');
        }
        $this->assertFalse($registry->containerExists('Laminas_View_Helper_InlineScript'));
        $helper = new Helper\InlineScript();
        $this->assertTrue($registry->containerExists('Laminas_View_Helper_InlineScript'));
    }

    public function testInlineScriptReturnsObjectInstance()
    {
        $placeholder = $this->helper->__invoke();
        $this->assertTrue($placeholder instanceof Helper\InlineScript);
    }
}

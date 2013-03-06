<?php
/**
 * Magento code generator
 *
 * PHP version 5.3
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  Tests
 * @package   Module
 * @author    Daniel Kocherga <dan.kocherga@gmail.com>
 * @copyright 2013 Daniel Kocherga (dan.kocherga@gmail.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/dankocherga/MTool
 */

namespace MTool\Bundle\Module;

/**
 * Module creator test case
 *
 * @category Tests
 * @package  Module
 * @author   Daniel Kocherga <dan@oggettoweb.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://github.com/dankocherga/MTool
 */
class CreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Mock module 
     * 
     * @param string $company Company
     * @param string $name    Name
     *
     * @return \MTool\Core\Module
     */
    private function _mockModule($company, $name)
    {
        $module = $this->getMockBuilder('\MTool\Core\Module')
            ->disableOriginalConstructor()
            ->getMock();
        $module->expects($this->any())->method('getCompany')
            ->will($this->returnValue($company));
        $module->expects($this->any())->method('getName')
            ->will($this->returnValue($name));
        return $module;
    }

    /**
     * Mock environment 
     * 
     * @param string $workingDir Working dir
     *
     * @return \MTool\Core\Environment\IEnvironment
     */
    private function _mockEnvironment($workingDir)
    {
        $env = $this->getMock('\MTool\Core\Environment\IEnvironment');
        $env->expects($this->any())->method('getWorkingDir')
            ->will($this->returnValue($workingDir));
        return $env;
    }

    /**
     * Create adds module etc directory to local pool 
     * 
     * @return void
     * @test
     */
    public function createAddsModuleEtcDirectoryToLocalPool()
    {
        $module = $this->_mockModule('MyCompany', 'MyModule');
        $env = $this->_mockEnvironment('/root');

        $filesystem = $this->getMock('\MTool\Core\Storage\IStorage');
        $filesystem->expects($this->once())->method('mkdir')
            ->with($this->equalTo('/root/app/code/local/MyCompany/MyModule/etc'));

        $templateFactory = $this->getMockBuilder('\MTool\Bundle\Module\TemplateFactory')
            ->disableOriginalConstructor()->getMock();
        $templateFactory->expects($this->once())->method('getModuleConfig')
            ->will($this->returnValue($this->getMock('\MTool\Core\Template\ITemplate')));
        $templateFactory->expects($this->once())->method('getModuleGlobalConfig')
            ->will($this->returnValue($this->getMock('\MTool\Core\Template\ITemplate')));

        $creator = new Creator($filesystem, $env, $templateFactory);
        $creator->create($module);
    }

    /**
     * Create writes parsed template content to module config file
     * 
     * @return void
     * @test
     */
    public function createWritesParsedTemplateContentToModuleConfigFile()
    {
        $module = $this->_mockModule('MyCompany', 'MyModule');
        $env = $this->_mockEnvironment('/root');

        $template = $this->getMock('\MTool\Core\Template\ITemplate');
        $template->expects($this->any())->method('parse')
            ->will($this->returnValue('content'));

        $templateFactory = $this->getMockBuilder('\MTool\Bundle\Module\TemplateFactory')
            ->disableOriginalConstructor()->getMock();
        $templateFactory->expects($this->any())->method('getModuleConfig')
            ->with($this->equalTo($module))
            ->will($this->returnValue($template));
        $templateFactory->expects($this->any())->method('getModuleGlobalConfig')
            ->will($this->returnValue($this->getMock('\MTool\Core\Template\ITemplate')));

        $filesystem = $this->getMock('\MTool\Core\Storage\IStorage');
        $filesystem->expects($this->at(1))->method('write')->with(
            $this->equalTo('/root/app/code/local/MyCompany/MyModule/etc/config.xml'),
            $this->equalTo('content')
        );

        $creator = new Creator($filesystem, $env, $templateFactory);
        $creator->create($module);
    }

    /**
     * Create writes parsed template content to module global config file
     * 
     * @return void
     * @test
     */
    public function createWritesParsedTemplateContentToModuleGlobalConfigFile()
    {
        $module = $this->_mockModule('MyCompany', 'MyModule');
        $env = $this->_mockEnvironment('/root');

        $template = $this->getMock('\MTool\Core\Template\ITemplate');
        $template->expects($this->any())->method('parse')
            ->will($this->returnValue('content'));
        $templateFactory = $this->getMockBuilder('\MTool\Bundle\Module\TemplateFactory')
            ->disableOriginalConstructor()->getMock();
        $templateFactory->expects($this->any())->method('getModuleGlobalConfig')
            ->with($this->equalTo($module))
            ->will($this->returnValue($template));
        $templateFactory->expects($this->any())->method('getModuleConfig')
            ->will($this->returnValue($this->getMock('\MTool\Core\Template\ITemplate')));

        $filesystem = $this->getMock('\MTool\Core\Storage\IStorage');
        $filesystem->expects($this->at(2))->method('write')->with(
            $this->equalTo('/root/app/etc/modules/MyCompany_MyModule.xml'),
            $this->equalTo('content')
        );

        $creator = new Creator($filesystem, $env, $templateFactory);
        $creator->create($module);
    }
}

<?php

/*
 * This file is part of the ReportBundle package
 * 
 * (c) symball <http://simonball.me>
 * 
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

namespace Symball\ReportBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symball\ReportBundle\DependencyInjection\Compiler\ReportPatternPass;
use Symball\ReportBundle\DependencyInjection\Compiler\ReportStylePass;

class SymballReportBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ReportPatternPass());
        $container->addCompilerPass(new ReportStylePass());
    }
}

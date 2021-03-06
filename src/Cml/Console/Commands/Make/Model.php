<?php
/* * *********************************************************
 * [cmlphp] (C)2012 - 3000 http://cmlphp.com
 * @Author  linhecheng<linhechengbush@live.com>
 * @Date: 2016/11/2 14:07
 * @version  @see \Cml\Cml::VERSION
 * cmlphp框架 创建Model命令
 * *********************************************************** */

namespace Cml\Console\Commands\Make;

use Cml\Cml;
use Cml\Console\Command;
use Cml\Console\Format\Colour;
use Cml\Console\IO\Output;
use InvalidArgumentException;
use RuntimeException;

/**
 * 创建Model
 *
 * @package Cml\Console\Commands\Make
 */
class Model extends Command
{
    protected $description = "Create a new model class";

    protected $arguments = [
        'name' => 'The name of the class'
    ];

    protected $options = [
        '--table' => 'tablename',
        '--template=xx' => 'Use an alternative template',
        '--dirname=xx' => 'the model dir name default:`Model`',
    ];

    protected $help = <<<EOF
this command command allows you to create a new Model class
eg:
`php index.php make:model web/test-blog/Category`  this command will create a Model

<?php
namespace web\test\Model\blog;

use Cml\Model;

class CategoryModel extends Model
{
    protected \$table = 'category';
}
EOF;


    /**
     * 创建Model
     *
     * @param array $args 参数
     * @param array $options 选项
     */
    public function execute(array $args, array $options = [])
    {
        $tableName = $options['table'] ?? false;
        if (!$tableName) {
            throw new InvalidArgumentException(sprintf(
                'The option table "%s" is invalid. eg: user',
                $tableName
            ));
        }

        $template = $options['template'] ?? false;
        $template || $template = __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'Model.php.dist';
        $dirName = ($options['dirname'] ?? '') ?: 'Model';

        list($namespace, $module) = explode('-', trim($args[0], '/\\'));
        if (!$module) {
            $namespace = explode('/', $namespace);
            $module = array_pop($namespace);
            $namespace = implode('/', $namespace);
        }

        if (!$namespace) {
            throw new InvalidArgumentException(sprintf(
                'The arg name "%s" is invalid. eg: web-Blog/Category',
                $args[0]
            ));
        }

        $path = Cml::getApplicationDir('apps_path') . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR
            . $dirName . DIRECTORY_SEPARATOR;
        $component = explode('/', trim(trim($module, '/')));

        if (count($component) > 1) {
            $className = ucfirst(array_pop($component)) . 'Model';
            $component = implode(DIRECTORY_SEPARATOR, $component);
            $path .= $component . DIRECTORY_SEPARATOR;
            $component = '\\' . $component;
        } else {
            $className = ucfirst($component[0]) . 'Model';
            $component = '';
        }

        if (!is_dir($path) && false == mkdir($path, 0700, true)) {
            throw new RuntimeException(sprintf(
                'The path "%s" could not be create',
                $path
            ));
        }

        $contents = strtr(file_get_contents($template), [
            '$namespace' => str_replace('/', '\\', $namespace),
            '$component' => $component,
            '$dirName' => $dirName,
            '$className' => $className,
            '$tableName' => $tableName
        ]);

        $file = $path . $className . '.php';
        if (is_file($file)) {
            throw new RuntimeException(sprintf(
                'The file "%s" is exist',
                $file
            ));
        }

        if (false === file_put_contents($file, $contents)) {
            throw new RuntimeException(sprintf(
                'The file "%s" could not be written to',
                $path
            ));
        }

        $this->info("Model created successfully. ");
    }
}

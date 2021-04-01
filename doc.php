<?php

/** Class inspector */
class doc
{
    /** Name of class or array of names to inspect */
    static  $classes;
    /** Array of all class names on init */
    static array $declaredClasses;
    /** Array of current options */
    static array $options;
    /** All supported options and defaults */
    const OPTIONS = [
        'classHide' => [],
        'methodHide' => ['__construct', '__destruct', '__get'],
        'commentFormat' => true,
        'valueFormat' => true,
        'onlyPublic' => true,
        'methodOnly' => true,
        'varHide' => true,
        'typeHide' => true,
        'paramFormat' => true,
        'htmlUseH' => false,
    ];
    /** Used for custom list of classes and options 
     * @param string|array $classes
     */
    function __construct($classes = [], array $options = self::OPTIONS)
    {
        self::init($options, $classes);
    }
    /** Get declared classes */
    static function init(array $options = self::OPTIONS, $classes = [])
    {
        if (self::$classes === null) {
            self::$declaredClasses = get_declared_classes();
            $classes = is_array($classes) ? $classes : (array)$classes;
            self::$classes = $classes ? $classes : self::$classes;
            self::$options = array_merge(self::OPTIONS, $options);
        }
    }
    /** Remove special symbols and spaces */
    private static function clrDoc(string $str): string
    {
        return (self::$options['commentFormat']) ? trim(preg_replace(["'([/*])'", "'([\r\n])'", '/\s\s+/'], ' ', $str)) : $str;
    }
    /** used for export inbuilt method of ReflectionClass toString() */
    static function toString(): string
    {
        self::$classes ??= array_diff(self::$declaredClasses, self::$classes);
        $r = [];
        foreach (self::$classes as $class) {
            if (!class_exists($class) || in_array($class, self::$options['classHide'])) continue;

            $c = new ReflectionClass($class);
            $r[] = $c->__toString();
        }
        return implode("<br>" . PHP_EOL, $r);
    }
    /** Exporting all classes information in assoc array */
    static function toArray(): array
    {
        self::$classes ??= array_diff(get_declared_classes(), self::$declaredClasses);
        $r = [];
        foreach (self::$classes as $class) {
            if (!class_exists($class) || in_array($class, self::$options['classHide'])) continue;

            $c = new ReflectionClass($class);
            if ($docClass = $c->getDocComment()) $r[$class]['description'] = self::clrDoc($docClass);

            foreach ($c->getConstants() as $const => $constVal) {
                $cn = new ReflectionClassConstant($class, $const);
                $r[$class]['const'] = [
                    'description' => self::clrDoc($cn->getDocComment()),
                    'type' => self::getType($cn),
                    'value' => self::printV($constVal),
                ];
                if (self::typeHide($r[$class]['const'])) unset($r[$class]['const']['type']);
            }

            if (empty(self::$options['varHide'])) {
                foreach ($c->getProperties() as $prop) {
                    $p = new ReflectionProperty($class, $prop->name);
                    $r[$class]['var'][$prop->name] = [
                        'description' => self::clrDoc($p->getDocComment()),
                        'type' => self::getType($p),
                        'value' => phpversion() > 8 && $p->hasDefaultValue() ? $p->getDefaultValue() : 'Will appear in php version > 8.0',
                        'valueType' => $p->hasType() ? $p->getType()->getName() : '',
                    ];
                    if (phpversion() > 8) $r[$class]['var'][$prop->name]['value'] = $p->hasDefaultValue() ? $p->getDefaultValue() : '';

                    if (self::onlyPublic($r[$class]['var'][$prop->name])) unset($r[$class]['var'][$prop->name]);
                }
            }

            foreach (get_class_methods($class) as $method) {
                if (in_array($method,  self::$options['methodHide'])) continue;

                $m = new ReflectionMethod($class, $method);
                if (strcasecmp($m->getDeclaringClass()->name, $class)) continue; //not inherit
                $typeMethod = self::getType($m);
                $docMethod = $m->getDocComment();
                $methodArr = [];
                if ($docMethod) $methodArr['description'] = self::clrDoc($docMethod);
                $methodArr['type'] = $typeMethod;
                if ($m->hasReturnType()) $methodArr['return'] = $m->getReturnType()->getName();

                foreach ($m->getParameters() as $param) {
                    $pm = new ReflectionParameter([$class, $method], $param->name);
                    $methodArr['param']['$' . $param->name] = [
                        'required' => !$pm->isOptional(),
                        'type' => self::getType($pm),
                        'valueType' => $pm->hasType() ? $pm->getType()->getName() : '',
                    ];
                    if (!$methodArr['param']['$' . $param->name]['required']) $methodArr['param']['$' . $param->name]['value'] = $pm->isDefaultValueAvailable() ? self::printV($pm->getDefaultValue()) : '';
                }
                if (self::onlyPublic($methodArr)) unset($methodArr);
                else {
                    if (self::typeHide($methodArr)) unset($methodArr['type']);

                    if (!empty(self::$options['paramFormat'])) {
                        if (isset($methodArr['param'])) {
                            $paramType = $methodArr['param']['$' . $param->name]['valueType'];
                            $paramType = $paramType ? $paramType . ' ' : '';
                            $r[$class][$method . "(" . ($paramType) . '$' . $param->name . (empty($methodArr['param']['$' . $param->name]['value']) ? '' : '=' . $methodArr['param']['$' . $param->name]['value']) . ")"] = $methodArr['description'] ?? '';
                        } else {
                            $r[$class][$method . "()"] = $methodArr['description'] ?? '';
                        }
                    } else {
                        if (empty(self::$options['methodOnly'])) $r[$class]['method'][$method] = $methodArr;
                        else $r[$class][$method] = $methodArr;
                    }
                }
            }
        }
        return $r;
    }
    private static function onlyPublic($v)
    {
        return !empty(self::$options['onlyPublic']) && stripos($v['type'] ?? '', 'public') === false;
    }
    private static function typeHide($v)
    {
        return !empty(self::$options['typeHide']) && isset($v['type']);
    }
    private static function html_encode($value, bool $useH = true)
    {
        static $depth = 0;
        $depth++;
        $html = '';
        $h = $useH ? ["<h$depth>", "</h$depth>"] : ['', ''];
        foreach ($value as $n => $v) $html .= "<ul><li>{$h[0]}$n: " . (is_array($v) ? $h[1] . self::html_encode($v, $useH) . "</ul>" : self::printV($v) . "$h[1]</li></ul>");
        $depth--;
        return $html;
    }
    /** Generate html unordered list */
    static function toHtml()
    {
        return self::html_encode(self::toArray(), !empty(self::$options['htmlUseH']));
    }
    /** Generate JSON */
    static function toJson()
    {
        return json_encode(self::toArray());
    }
    /** Universal getter for methods and properties types * @return string public protected private promoted static */
    private static function getType($obj)
    {
        $r = [];
        foreach (['Public', 'Protected', 'Private', 'Promoted', 'Static'] as $name) {
            if (method_exists($obj, 'is' . $name) && $obj->{'is' . $name}()) $r[] = $name;
        }
        return implode(' ', $r);
    }
    /** Pretty print any values (array null bool) to inline */
    private static function printV($value)
    {
        if (empty(self::$options['valueFormat']) || is_string($value)) return $value;
        $value = json_encode($value);
        $replace = [
            '"' => "'",
            ':' => '=>',
            '{' => '[',
            '}' => ']',
            ',' => ', ',
        ];
        $value = str_replace(array_keys($replace), array_values($replace), $value);
        return $value;
    }
}

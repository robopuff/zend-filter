<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License;
 */

namespace Zend\Filter;

use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Null extends AbstractFilter
{
    const TYPE_BOOLEAN      = 1;
    const TYPE_INTEGER      = 2;
    const TYPE_EMPTY_ARRAY  = 4;
    const TYPE_STRING       = 8;
    const TYPE_ZERO_STRING  = 16;
    const TYPE_FLOAT        = 32;
    const TYPE_ALL          = 63;

    /**
     * @var array
     */
    protected $constants = array(
        self::TYPE_BOOLEAN     => 'boolean',
        self::TYPE_INTEGER     => 'integer',
        self::TYPE_EMPTY_ARRAY => 'array',
        self::TYPE_STRING      => 'string',
        self::TYPE_ZERO_STRING => 'zero',
        self::TYPE_FLOAT       => 'float',
        self::TYPE_ALL         => 'all',
    );

    /**
     * @var array
     */
    protected $options = array(
        'type'         => self::TYPE_ALL,
    );

    /**
     * Constructor
     *
     * @param string|array|Traversable $options OPTIONAL
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            if ($options instanceof Traversable) {
                $options = iterator_to_array($options);
            }

            if (!is_array($options)) {
                $this->setType($options);
            } else {
                $this->setOptions($options);
            }
        }
    }

    /**
     * Set boolean types
     *
     * @param  integer|array $type
     * @throws Exception\InvalidArgumentException
     * @return Boolean
     */
    public function setType($type = null)
    {
        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    $detected += $value;
                } elseif (in_array($value, $this->constants)) {
                    $detected += array_search($value, $this->constants);
                }
            }

            $type = $detected;
        } elseif (is_string($type) && in_array($type, $this->constants)) {
            $type = array_search($type, $this->constants);
        }

        if (!is_int($type) || ($type < 0) || ($type > self::TYPE_ALL)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unknown type value "%s" (%s)',
                $type,
                gettype($type)
            ));
        }

        $this->options['type'] = $type;
        return $this;
    }

    /**
     * Returns defined boolean types
     *
     * @return int
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns null representation of $value, if value is empty and matches
     * types that should be considered null.
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $type = $this->getType();

        // FLOAT (0.0)
        if ($type >= self::TYPE_FLOAT) {
            $type -= self::TYPE_FLOAT;
            if (is_float($value) && ($value == 0.0)) {
                return null;
            }
        }

        // STRING ZERO ('0')
        if ($type >= self::TYPE_ZERO_STRING) {
            $type -= self::TYPE_ZERO_STRING;
            if (is_string($value) && ($value == '0')) {
                return null;
            }
        }

        // STRING ('')
        if ($type >= self::TYPE_STRING) {
            $type -= self::TYPE_STRING;
            if (is_string($value) && ($value == '')) {
                return null;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type >= self::TYPE_EMPTY_ARRAY) {
            $type -= self::TYPE_EMPTY_ARRAY;
            if (is_array($value) && ($value == array())) {
                return null;
            }
        }

        // INTEGER (0)
        if ($type >= self::TYPE_INTEGER) {
            $type -= self::TYPE_INTEGER;
            if (is_int($value) && ($value == 0)) {
                return null;
            }
        }

        // BOOLEAN (false)
        if ($type >= self::TYPE_BOOLEAN) {
            $type -= self::TYPE_BOOLEAN;
            if (is_bool($value) && ($value == false)) {
                return null;
            }
        }

        return $value;
    }
}

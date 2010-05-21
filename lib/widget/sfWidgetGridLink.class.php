<?php

/*
 * This file is part of the symfony package.
 * (c) Leon van der Ree <leon@fun4me.demon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfWidgetGridLink extends sfWidgetGrid
{

  /**
   * Configures the current widget.
   *
   * This method allows each widget to add options or HTML attributes
   * during widget creation.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of HTML attributes
   *
   * @see __construct()
   */
  public function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addRequiredOption('key_column');
    $this->addRequiredOption('action', null);
    $this->addOption('mapping', array());

    // options for render
    $this->addOption('label', null);

  }

  /**
   * Returns the internal uri for the current widget
   *
   * @return string
   */
  public function getUri()
  {
    $uri = url_for($this->getOption('action'), array(), true).'?';

    $key_column = $this->getOption('key_column');
    if ($key_column)
    {
      $source = $this->getGrid()->getDataSource();
      $key = $key_column;

      if (isset($this->mapping[$key]))
      {
        $key = $this->mapping[$key];
      }

      $uri .= $key.'='.$source[$key_column];
    }

    return $uri;
  }

  public function getKeyColumn()
  {
    if ($this->getOption('key_column'))
    {
      return $this->getOption('key_column');
    }

    throw new UnexpectedValueException(sprintf("For use this method, it's necesary a valid 'key_column' option in widget '%s' class.", getclass($this)));
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url', 'Tag'));

    return link_to($name, $this->getUri(), $attributes);
  }
}
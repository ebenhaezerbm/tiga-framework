<?php
/*
 * Some of the codes are taken from illuminate/html & AdamWathan/form;
 */
namespace Tiga\Framework\Html;

class HtmlBuilder {
	/**
     * Convert an HTML string to entities.
     *
     * @param  string  $value
     * @return string
     */
    public function entities($value)
    {
    	if(is_array($value))
    	{
    		$newValue = array();
    		foreach ($value as $key => $val) {
    			$newValue[$key] = htmlentities($val, ENT_QUOTES, 'UTF-8', false);
    		}

    		return $newValue;
    	}

        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Convert entities to HTML characters.
     *
     * @param  string  $value
     * @return string
     */
    public function decode($value)
    {
    	if(is_array($value))
    	{
    		$newValue = array();
    		foreach ($value as $key => $val) {
    			$newValue[$key] = html_entity_decode($val, ENT_QUOTES, 'UTF-8');
    		}

    		return $newValue;
    	}
    	
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }

    /**
	 * Build an HTML attribute string from an array.
	 *
	 * @param  array  $attributes
	 * @return string
	 */
	public function attributes($attributes)
	{
		$html = array();

		// For numeric keys we will assume that the key and the value are the same
		// as this will convert HTML attributes such as "required" to a correct
		// form like required="required" instead of using incorrect numerics.
		foreach ((array) $attributes as $key => $value)
		{
			$element = $this->attributeElement($key, $value);

			if ( ! is_null($element)) $html[] = $element;
		}

		return count($html) > 0 ? ' '.implode(' ', $html) : '';
	}

	/**
	 * Build a single attribute element.
	 *
	 * @param  string  $key
	 * @param  string  $value
	 * @return string
	 */
	protected function attributeElement($key, $value)
	{
		if (is_numeric($key)) $key = $value;

		if ( ! is_null($value)) return $key.'="'.$this->entities($value).'"';
	}
}
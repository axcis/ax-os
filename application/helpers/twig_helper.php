<?php

/**
 * twig_helper
 * @author takanori_gozu
 */

//プルダウン
if ( ! function_exists('twig_func_select'))
{
	function twig_func_select($name, $list, $selected = '', $param = '')
	{
		return form_dropdown($name, $list, $selected, $param);
	}
}

//チェックボックス
if ( ! function_exists('twig_func_checkbox'))
{
	function twig_func_checkbox($name, $key, $value, $id = '', $check_ids = array(), $entered = 0, $extra = '')
	{
		$label_id = $id != '' ? $id : $name. $key;
		$data = array('name' => $name, 'id' => $label_id);
		$checked = in_array($key, isset($check_ids) ? $check_ids : array()) ? true : false;
		$checkbox = form_checkbox($data, $key, $checked, $extra);
		if ($entered == 1) {
			return $checkbox. form_label($value, $label_id). "<br>";
		}
		return $checkbox. form_label($value, $label_id);
	}
}

//ラジオボタン
if ( ! function_exists('twig_func_radio'))
{
	function twig_func_radio($name, $key, $value, $id = '', $selected = array(), $entered = 0, $extra = '')
	{
		$label_id = $id != '' ? $id : $name. $key;
		$data = array('name' => $name, 'id' => $label_id);
		$checked = in_array($key, isset($selected) ? $selected : array()) ? true : false;
		$radio = form_radio($data, $key, $checked, $extra);
		if ($entered == 1) {
			return $radio. form_label($value, $label_id). "<br>";
		}
		return $radio. form_label($value, $label_id);
	}
}


?>
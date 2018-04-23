<?php

namespace ElasticWrapper;

class I18nEnum
{
	/**
	 * Decode language from ISO 639-1 Code to ElasticSearch standart
	 * @param  string $locale ISO 639-1 Code
	 * @return string         ElasticSearch language standart
	 */
	public static function decodeLanguage($locale = null)
	{
		if (empty($locale)) {
			return 'english';
		}
		$matrix = [
			'arabic' => 'ar',
			'armenian' => 'hy',
			'basque' => 'eu',
			'bengali' => 'bn',
			'brazilian' => 'pt-br',
			'bulgarian' => 'bg',
			'catalan' => 'ca',
			'cjk' => 'cjk',
			'czech' => 'cs',
			'danish' => 'da',
			'dutch' => 'nl',
			'english' => 'en',
			'finnish' => 'fi',
			'french' => 'fr',
			'galician' => 'gl',
			'german' => 'de',
			'greek' => 'el',
			'hindi' => 'hi',
			'hungarian' => 'hu',
			'indonesian' => 'id',
			'irish' => 'ga',
			'italian' => 'it',
			'latvian' => 'lv',
			'lithuanian' => 'lt',
			'norwegian' => 'no',
			'persian' => 'fa',
			'portuguese' => 'pt',
			'romanian' => '	ro',
			'russian' => 'ru',
			'sorani' => 'ku',
			'spanish' => 'es',
			'swedish' => 'sv',
			'turkish' => 'tr',
			'thai' => 'th',
		];
		foreach ($matrix as $id => $enum) {
			if (is_array($enum)) {
				if (in_array($locale, $enum)) {
					return $id;
				}
			} elseif ($enum === $locale) {
				return $id;
			}
		}
		return 'english';
	}
}

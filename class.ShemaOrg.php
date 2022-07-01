<?php

/**
 * Класс для генерации микроразметки Schema.org
 * Документация по микроразметке https://www.schema.org/
 */
class ShemaOrg {

    public $siteLogo = 'images/logo.svg';

    private $shemaOrgContext = 'http://schema.org';
	private $siteUrl = '';
	private $siteName = '';
	private $companyName = '';
	private $langCode = '';


	public function __construct(string $siteName = '', string $companyName = '')
    {
		$this->siteName = $siteName;
		$this->companyName = $companyName;

		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$this->siteUrl = $protocol.$_SERVER['HTTP_HOST'];
		$this->siteLogo = $this->siteUrl.$this->siteLogo;
   	}


    public function displayShemaOrg(array $content = [], string $type = 'Article')
    {
		if (empty($content)) {
			return '';
		}

		switch ($type) {
			case 'Article':
				$shemaOrgJson = $this->buildArticle($content);
				break;

			case 'BreadcrumbList':
				$shemaOrgJson = $this->buildBreadCrumbs($content);
				break;

            case 'FAQ':
                $shemaOrgJson = $this->buildFAQ($content);
                break;

            case 'Reviews':
                $shemaOrgJson = $this->buildReviews($content);
                break;

			default:
				$shemaOrgJson = $this->buildArticle($content);
				break;
		}

        return !empty($shemaOrgJson) ? json_encode($shemaOrgJson, JSON_UNESCAPED_UNICODE) : '';
	}


    private function buildArticle(array $content = [])
    {
		$shemaOrgObject = [
			"@context" => $this->shemaOrgContext,
			"@type"    => 'Article',
		];

		if (!empty($content['url'])) {
			$shemaOrgObject["mainEntityOfPage"] = $this->siteUrl.$content['url'];
		}

		if (!empty($content['title'])) {
			$shemaOrgObject["headline"] = $content['title'];
		}

		if (!empty($content['date'])) {
			$shemaOrgObject["datePublished"] = $content['date'];
		}

		if (!empty($content['date_modified'])) {
			$shemaOrgObject["dateModified"] = $content['date_modified'];
		}

		if (!empty($content['content'])) {
			$shemaOrgObject["articleBody"] = $content['content'];
		}

		if (!empty($content['author'])) {
			$shemaOrgObject["author"] = [
				"@type"	=> 'Person',
				"name"  => $content['author']
			];
		}

		if (!empty($content['editor'])) {
			$shemaOrgObject["author"] = [
				"@type"	=> 'Person',
				"name"  => $content['editor']
			];
		}

		$shemaOrgObject["publisher"] = [
			"@type" => "Organization",
			"name"  => !empty($this->companyName) ? $this->companyName : $this->siteName,
			"logo"  => [
				"@type"  => "ImageObject",
				"url"	 => $this->siteUrl.$this->siteLogo,
				"width"  => 1200,
				"height" => 146
			]
		];

		if (!empty($content['image'])) {
			$shemaOrg['image'] = $this->siteUrl.'imagine/'.$content['preset'].'/'.$content['image'];
		}

		return $shemaOrgObject;
	}


    private function buildBreadCrumbs(array $navigationList = [])
    {
        $i = 1;

		$shemaOrgObject = [
			"@context"		  => $this->shemaOrgContext,
			"@type"    		  => 'BreadcrumbList',
			"itemListElement" => [
				[
					"@type"    => "ListItem",
					"position" => $i,
					"item"	   => [
						"@id"  => $this->siteUrl,
						"name" => $this->siteName
					]
				]
			]
		];

		foreach ($navigationList as $key => $item) {
			$shemaOrgObject["itemListElement"][] = [
				"@type"    => "ListItem",
				"position" => ++$i,
				"item"	   => [
					"@id"  => $this->siteUrl.$item['url'],
					"name" => $item['title']
				]
			];
		}

		return $shemaOrgObject;
	}


    private function buildFAQ(array $faqList = [])
    {
        $shemaOrgObject = [
			"@context" => $this->shemaOrgContext,
			"@type"    => 'FAQPage',
		];

		foreach ($faqList as $key => $item) {
			$shemaOrgObject["mainEntity"][] = [
				"@type"          => "Question",
				"name"           => $item['question'],
				"acceptedAnswer" => [
					"@type" => "Answer",
					"text"  => $item['answer']
				]
			];
		}

        return $shemaOrgObject;
    }


    private function buildReviews(array $content = [])
    {
        return [];
    }
}
?>

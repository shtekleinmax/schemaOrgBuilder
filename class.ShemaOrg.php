<?php

/**
 * Класс для генерации микроразметки Schema.org
 * Документация по микроразметке https://www.schema.org/
 */
class ShemaOrg {

    public $siteLogo = 'images/logo.svg';
	public $compress = false;

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


    public function displayShemaOrg(array $content = [], string $type = 'Article'): string
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

			case 'Recipe':
				$shemaOrgJson = $this->buildRecipe($content);

			default:
				$shemaOrgJson = $this->buildArticle($content);
				break;
		}

		if (!empty($shemaOrgJson)) {
			$result = !empty($this->compress) ? json_encode($shemaOrgJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : json_encode($shemaOrgJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

			return $result.PHP_EOL;
		}

		return '';
	}


    private function buildArticle(array $content = []): array
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
			$shemaOrgObject["author"] = $this->getAuthors($content['author']);
		}

		if (!empty($content['editor'])) {
			$editors = explode(',', $content['editor']);

			foreach ($editors as $editor) {
				$shemaOrgObject["editor"][] = [
					"@type"	=> 'Person',
					"name"  => trim($editor),
				];
			}
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


    private function buildBreadCrumbs(array $navigationList = []): array
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


    private function buildFAQ(array $faqList = []): array
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

	private function buildRecipe(array $content = [])
	{
		$shemaOrgObject = [
			"@context" => $this->shemaOrgContext,
			"@type"    => 'Recipe',
			"name"	   => $content['title']
		];

		if (!empty($content['image'])) {
			$shemaOrg['image'] = $this->siteUrl.'imagine/'.$content['preset'].'/'.$content['image'];
		}

		if (!empty($content['author'])) {
			$shemaOrgObject['author'] = $this->getAuthors($content['author']);
		}

		if (!empty($content['date'])) {
			$shemaOrgObject["datePublished"] = $content['date'];
		}

		if (!empty($content['description'])) {
			$shemaOrgObject["description"] = $content['description'];
		}

	}


	private function getAuthors($authors = ''): array
	{
		if (empty($authors)) {
			return [];
		}

		if (is_string($authors)) {
			$authors = $this->convertAuthorsStringToArray($authors);
		}

		if (!is_array($authors)) {
			return [];
		}

		foreach ($authors as $author) {
			$authorInfo = [
				"@type"	=> !empty($author['is_organization']) ? 'Organization' : 'Person',
				"name"  => trim($author),
			];

			if (!empty($author['url'])) {
				$authorInfo['url'] = $author['url'];
			}

			if (!empty($author['job_title'])) {
				$authorInfo['jobTitle'] = $author['job_title'];
			}

			$shemaOrgAuthor[] = $authorInfo;
		}

		return $shemaOrgAuthor;
	}


	private function convertAuthorsStringToArray(string $authorsField = '')
	{
		$authors = explode(',', $authorsField);

		foreach ($authors as $author) {
			$result[] = [
				"name" => trim($author),
			];
		}

		return $result;
	}

}
?>

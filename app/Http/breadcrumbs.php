<?php

// Home
Breadcrumbs::register('home', function($breadcrumbs)
{
    $breadcrumbs->push('Главная', action('HomeController@index'));
});

// Search
Breadcrumbs::register('search', function($breadcrumbs)
{
	$breadcrumbs->parent('home');
    $breadcrumbs->push(" / " . 'Поиск', action('HomeController@search'));
});


// Category
Breadcrumbs::register('category', function($breadcrumbs, $category, $subcategory)
{
	$breadcrumbs->parent('home');
    $breadcrumbs->push(" / " . $category->name, action('HomeController@category', ['slug' => $category->slug]));
    $breadcrumbs->push(" / " . $subcategory->category_name, action('HomeController@category', ['slug' => $category->slug, 'subcategory' => $subcategory->category_slug]));
});

// Organization
Breadcrumbs::register('organization', function($breadcrumbs, $category, $subcategory, $organization)
{
	$breadcrumbs->parent('category', $category, $subcategory);
    $breadcrumbs->push(" / " . $organization->name, action('HomeController@organization', ['organization_id' => $organization->id, 'category_id' => $subcategory->id]));
});

// Branch
Breadcrumbs::register('branch', function($breadcrumbs, $categories, $category, $subcategory, $organization, $branch)
{
    if (!is_null($categories))
    {
        $breadcrumbs->parent('home');
        $breadcrumbs->push(' / ' . $organization->name, action('HomeController@organization', ['organization_id' => $organization->id, 'category_id' => 0]));
    }
    else
    {
        $breadcrumbs->parent('organization', $category, $subcategory, $organization);

        $breadcrumbs->push(" / " . "Филиал: " . $branch->name, action('HomeController@branch', ['branch_id' => $branch->id, 'category_id' => $subcategory->id]));
    }
});

// Branch_category
Breadcrumbs::register('branch_category', function($breadcrumbs, $category, $subcategory, $organization, $branch)
{
    $breadcrumbs->parent('home');
    $breadcrumbs->push(' / ' . $category->name, action('HomeController@category', ['slug' => $category->slug]));
    $breadcrumbs->push(' / ' . $subcategory->name, action('HomeController@category', ['slug' => $category->slug, 'subcategory' => $subcategory->slug]));
    $breadcrumbs->push(' / ' . $organization->name, action('HomeController@organization', ['organization_id' => $organization->id]));
    $breadcrumbs->push(' (' . $branch->address . ')', action('HomeController@branch', ['branch_id' => $branch->id, 'category_id' => $subcategory->id]));
});
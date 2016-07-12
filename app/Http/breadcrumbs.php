<?php

// Home
Breadcrumbs::register('home', function($breadcrumbs)
{
    $breadcrumbs->push('Главная', action('HomeController@index'));
});

// Home
Breadcrumbs::register('category', function($breadcrumbs, $category, $subcategory)
{
	$breadcrumbs->parent('home');
    $breadcrumbs->push($category->name, action('HomeController@category', ['slug' => $category->slug]));
    $breadcrumbs->push($subcategory->name, action('HomeController@category', ['slug' => $category->slug, 'subcategory' => $subcategory->slug]));
});

// Organization
Breadcrumbs::register('organization', function($breadcrumbs, $category, $subcategory, $organization)
{
	$breadcrumbs->parent('category', $category, $subcategory);
    $breadcrumbs->push($organization->name, action('HomeController@organization', ['organization_id' => $organization->id, 'category_id' => $subcategory->id]));
});

// Branch
Breadcrumbs::register('branch', function($breadcrumbs, $category, $subcategory, $organization, $branch)
{
	$breadcrumbs->parent('organization', $category, $subcategory, $organization);
    $breadcrumbs->push("Филиал: " . $branch->name, action('HomeController@branch', ['branch_id' => $branch->id, 'category_id' => $subcategory->id]));
});
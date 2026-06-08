<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;

class DemoController extends Controller
{

	public function branch()
	{
		$headerWhite = 'header-secondary';
		return view('langding.demo-branch', compact('headerWhite'));
	}

	public function about()
	{
		$headerWhite = 'header-secondary';
		return view('langding.demo-about', compact('headerWhite'));
	}

	public function postsCategory()
	{
		$headerWhite = 'header-secondary';
		return view('langding.demo-posts-category', compact('headerWhite'));
	}

	public function posts()
	{
		$headerWhite = 'header-secondary';
		return view('langding.demo-posts', compact('headerWhite'));
	}

	public function productsCategoryVenture()
	{
		return view('langding.demo-products-category-venture');
	}
}

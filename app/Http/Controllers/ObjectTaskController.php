<?php

namespace Modules\ObjectTask\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\ObjectTask\Services\ObjectCodeService;
use Modules\ObjectTask\Services\TaskCodeService;

class ObjectTaskController extends Controller
{
	public function __construct(
		protected TaskCodeService $taskCodes,
		protected ObjectCodeService $objectCodes,
	) {
	}
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		return view("objecttask::index");
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function taskCodes()
	{
		return response()->json($this->taskCodes->getTaskCodes());
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function categories()
	{
		return response()->json($this->objectCodes->getObjectCodes());
	}

	/**
	 * Show the specified resource.
	 */
	public function contents($id)
	{
		return response()->json($this->objectCodes->getContentById($id)->contents);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit($id)
	{
		return view("objecttask::edit");
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, $id)
	{
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy($id)
	{
	}
}

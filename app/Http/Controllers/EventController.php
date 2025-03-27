<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use Symfony\Contracts\Service\Attribute\Required;

class EventController extends Controller
{
    use CanLoadRelationships; 

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('index', 'show');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $relations = ['user', 'attendees', 'attendees.user'];
        $query = $this->loadRelationships(Event::query(), $relations); 


        // return  EventResource::collection(Event::with('user')->paginate()); 

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $event = Event::create([
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => $request->user()->id
        ]);

        return new EventResource($event);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('user', 'attendees');
        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $event->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time'
            ])
        );
        return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successful'
        ]);
    }
}

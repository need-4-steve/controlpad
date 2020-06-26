<?php namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Note;

class NoteController extends Controller
{
    /**
    * Gets all of the notes for all the users.
    *
    * @return \Illuminate\Http\Response
    */
    public function getIndex()
    {
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $notes = Note::all();
            return response()->json($notes, HTTP_SUCCESS);
        }
        return response()->json([$messages['Unauthorized']], HTTP_UNAUTHORIZED);
    }

    /**
    * Gets all of the notes of the same type.
    *
    * @return \Illuminate\Http\Response
    */
    public function getRelatedNotes($id, $type)
    {
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $type = 'App\\Models\\' . $type;
            $notes = Note::with('author')
                        ->where('noteable_type', $type)
                        ->where('noteable_id', $id)
                        ->orderBy('updated_at', 'desc')
                        ->get();
            return response()->json($notes, HTTP_SUCCESS);
        }
        return response()->json([$messages['Unauthorized']], HTTP_UNAUTHORIZED);
    }

    /**
    * Create an new note.
    *
    * @return \Illuminate\Http\Response
    */
    public function postCreate(Request $request)
    {
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $this->validate($request, Note::$rules);
            $request['noteable_type'] = 'App\\Models\\' . $request['noteable_type'];
            $request['user_id'] = auth()->id();
            $note = Note::create($request->all());
            return response()->json($note, HTTP_SUCCESS);
        }
        return response()->json([$messages['Unauthorized']], HTTP_UNAUTHORIZED);
    }

    /**
    * Edit an note.
    *
    * @return \Illuminate\Http\Response
    */
    public function putEdit(Request $request, $id)
    {
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $this->validate($request, Note::$rules);
            $note = Note::find($id);
            $note->update($request->all());
            return response()->json($note, HTTP_SUCCESS);
        }
        return response()->json([$messages['Unauthorized']], HTTP_UNAUTHORIZED);
    }

    /**
    * delete an note.
    *
    * @return \Illuminate\Http\Response
    */
    public function deleteDelete($id)
    {
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            Note::destroy($id);
            return response()->json(['Successfully deleted.'], 200);
        }
        return response()->json(['You are not authorized to delete notes.'], HTTP_UNAUTHORIZED);
    }
}

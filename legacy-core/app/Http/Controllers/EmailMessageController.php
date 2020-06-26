<?php namespace App\Http\Controllers;

use Input;
use Redirect;
use Validator;
use App\Models\EmailMessage;
use App\Models\Lead;
use App\Models\MessageRecipient;
use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\EmailRepository;
use Mail;

class EmailMessageController extends Controller
{
    public function __construct(EmailRepository $emailRepo)
    {
        $this->emailRepo = $emailRepo;
    }
    /**
     * Display a listing of emailMessages
     *
     * @return Response
     */
    public function index()
    {

        return view('emailMessage.index');
    }

    /**
     * Show the form for creating a new emailMessage
     *
     * @return Response
     */
    public function create()
    {
        return view('emailMessage.create');
    }

    /**
     * Store a newly created emailMessage in storage.
     *
     * @return Response
     */
    public function store()
    {
        $validator = Validator::make($data = request()->all(), EmailMessage::$rules);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        EmailMessage::create($data);

        return Redirect::route('emailMessages.index')->with('message', 'Email Message created.');
    }

    /**
     * Display the specified emailMessage.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $emailMessage = EmailMessage::findOrFail($id);
        $messageRecipients = MessageRecipient::where('messagable_type', 'Email')->where('messagable_id', $id)->get();
        $recipients = [];
        foreach ($messageRecipients as $recipient) {
            if ($recipient->recipientable_type == 'User') {
                $person = User::find($recipient->recipientable_id);
                $person->type = 'User';
            } else {
                $person = Lead::find($recipient->recipientable_id);
                $person->type = 'Lead';
            }
            $recipients[] = $person;
        }
        // echo '<pre>'; print_r($recipients); echo '</pre>';
        // exit;
        $sender = User::find($emailMessage->sender_id);
        return view('emailMessage.show', compact('emailMessage', 'sender', 'recipients'));
    }

    /**
     * Show the form for editing the specified emailMessage.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $emailMessage = EmailMessage::find($id);

        return view('emailMessage.edit', compact('emailMessage'));
    }

    /**
     * Update the specified emailMessage in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $emailMessage = EmailMessage::findOrFail($id);

        $validator = Validator::make($data = request()->all(), EmailMessage::$rules);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $emailMessage->update($data);

        return Redirect::route('emailMessages.show', $id)->with('message', 'Email Message updated.');
    }

    /**
     * List the recipients of the specified email
     *
     * @param  int  $id
     * @return Response
     */
    public function recipients($id)
    {
        $emailMessage = EmailMessage::findOrFail($id);
        return view('emailMessage.recipients', compact('emailMessage'));
    }

    /**
     * Remove the specified emailMessage from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        EmailMessage::destroy($id);

        return Redirect::route('email-messages.index')->with('message', 'Email Message deleted.');
    }

    /**
     * Remove emailMessages.
     */
    public function delete()
    {
        foreach (request()->get('ids') as $id) {
            EmailMessage::destroy($id);
        }
        if (count(request()->get('ids')) > 1) {
            return Redirect::route('email-messages.index')->with('message', 'Email Messages deleted.');
        } else {
            return Redirect::back()->with('message', 'Email Message deleted.');
        }
    }

    /**
     * Disable emailMessages.
     */
    public function disable()
    {
        foreach (request()->get('ids') as $id) {
            EmailMessage::find($id)->update(['disabled' => 1]);
        }
        if (count(request()->get('ids')) > 1) {
            return Redirect::route('email-messages.index')->with('message', 'Email Messages disabled.');
        } else {
            return Redirect::back()->with('message', 'Email Message disabled.');
        }
    }

    /**
     * Enable emailMessages.
     */
    public function enable()
    {
        foreach (request()->get('ids') as $id) {
            EmailMessage::find($id)->update(['disabled' => 0]);
        }
        if (count(request()->get('ids')) > 1) {
            return Redirect::route('email-messages.index')->with('message', 'Email Messages enabled.');
        } else {
            return Redirect::back()->with('message', 'Email Message enabled.');
        }
    }

    public function contactUs(Request $request)
    {
        $rules = ['name' => 'required', 'email' => 'required|email', 'body' => 'required|min:15'];
        $this->validate($request, $rules, ['name.required' => 'You must enter a name',
            'email.required' => 'You must enter a email',
            'body.required' => 'You need to enter a message of at lest 15 characters']);

        if ($rep = session()->get('store_owner')) {
            $email_to = $rep->email;
        } else {
            $email_to = config('site.customer_service_email');
        }
        $data_object = $request->all();
        $message_data['data'] = $request;
        // get user
        Mail::send('emails.contact', $message_data, function ($message) use ($data_object, $email_to) {
            $message->from($data_object['email'], $data_object['name']);
            $message->to($email_to, 'Customer Service')
                ->subject(config('site.domain') . ' contact form: ' . $data_object['subject_line']);
        });
        return redirect()->back()->with('message', 'Message sent.');
    }

    public function emailReports()
    {
        return view('reports.emails');
    }

    public function customEmailEdit($title)
    {
        $email = $this->emailRepo->emailShow($title);
        return view('emails.customEmailEdit', compact('email'));
    }
}

@if(Auth::user()->hasRole(['Customer']) && Auth::user()->password == null)
    <div class="well inline-block">
        <strong>Your account is almost complete!</strong>
        <table class="table no-bottom">
            <tr>
                <td>Create a password for tracking orders and faster checkout</td>
                <td>
                    <a class="btn btn-default" href="/users/{{ Auth::user()->id }}/edit">Complete <i class="lnr lnr-chevron-right"></i></a>
                </td>
            </tr>
        </table>
    </div>
@endif

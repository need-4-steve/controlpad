<div class="panel panel-default" ng-cloak ng-controller="noteController" ng-init="loadNotes({{ $noteable_id }}, '{{ $model }}')">
	<div class="panel-heading">
		<h2 class="panel-title align-center">Notes<a class="pull-right" href="" title="Add Note"></a></h2>
	</div>
	<div class="panel-body">
		<ul class="list-group notes">
			<h4 class="success-message align-center noteSuccess" ng-show="noteSuccess">@{{ noteSuccessMessage }}</h4>
			<h4 class="success-message align-center deleted" ng-show="deleted">@{{ deletedMessage }}</h4>
			<li class="list-group-item note" ng-repeat="note in newNotes | orderBy:'-updated_at'">
				<i class="lnr lnr-cross pull-right pointer" ng-click="deleteNote($index)"></i>
				<strong class="no-margin triggerSlide" ng-click="toggleAccordion($index)"></strong>
				<textarea class="noteTextArea noteSlide"
						  rows="5"
						  ng-show="note.active == 1"
						  ng-disabled="note.saved == false"
						  ng-model="noteBody">
				</textarea>
				<button class="btn btn-xs"
						id="addNote"
						ng-click="saveNote(noteBody)"
						ng-show="note.active == 1">Save Note
				</button>
			</li>
			<li class="list-group-item note" ng-repeat="note in notes | orderBy:'-updated_at' | limitTo: limit">
				<i class="lnr lnr-cross pull-right pointer" ng-click="deleteNote($index)"></i>
				<strong class="no-margin triggerSlide" ng-click="toggleAccordion($index)">
					by @{{ note.author.first_name }} @{{ note.author.last_name }} on @{{ note.updated_at | myDateFormat }}
				</strong><br>
				<small ng-bind="note.body"></small>
				<textarea class="noteTextArea noteSlide"
						  rows="5"
						  ng-show="note.active == 1"
						  ng-disabled="note.saved == false"
						  ng-bind="note.body"
						  ng-model="noteBody2">
				</textarea>
				<button class="btn btn-xs"
						id="addNote"
						ng-click="updateNote(noteBody2)"
						ng-show="note.active == 1">Update Note
				</button>
			</li>
		</ul>
	</div>
	<div class="panel-footer align-center" ng-class="{ 'space-around' : notes.length >= 8 }">
		<button class="cp-button-standard"
				id="addNote"
				ng-click="addNote({{ $noteable_id }}, '{{ $model }}')">Add Note
		</button>
		<button class="cp-button-standard"
				id="showMoreNotes"
				ng-hide="hideButton"
				ng-click="showMoreNotes()"
				ng-if="notes.length >= 8">Show More Notes
		</button>
	</div>
</div>

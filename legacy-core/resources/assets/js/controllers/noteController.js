(function(){
'use strict';
    angular
    .module('app')
    .controller('noteController', noteController)
    noteController.$inject = ['$scope', '$http'];
	function noteController ($scope, $http){
		$scope.limit = 8;
		$scope.noteSuccess = false;
		$scope.noteSuccessMessage = "Note Saved!";
		$scope.notes = [];
        $scope.newNotes = [];
        $scope.deleted = false;

		$scope.loadNotes = function(noteable_id, model){
			$http.get('/api/v1/notes/related-notes/' + noteable_id + "/" + model).success(function(response){
				$scope.notes = response;
				$scope.noteable_type = model;
				$scope.noteable_id = noteable_id;
			});
		};

      $scope.addNote = function(){
            $scope.newNote = {
                noteable_id: $scope.noteable_id,
                noteable_type: $scope.noteable_type,
                active: 1
            };
              $scope.newNotes.push($scope.newNote);

      };

        $scope.saveNote = function(noteBody){
            $scope.newNote = {
                body: noteBody,
                noteable_id: $scope.noteable_id,
                noteable_type: $scope.noteable_type,
                active: 1
            }
    		$http.post('/api/v1/notes/create', $scope.newNote)
                .then(function(response){
                    console.log(response);
                    response.active = true;
                    $scope.saved = true;
                    $scope.noteSuccess = true;
                    setTimeout(function (){
                        $scope.$apply(function(){
                            $scope.noteSuccess = false;
                        });
                    }, 2000);
        		}).catch((err) => {
                    console.log('error', err)
                });
        }

		$scope.updateNote = function(noteBody2){
			angular.forEach($scope.notes, function(note){
				if(note.active == 1){
                    $scope.updatedNote = {
                        body: noteBody2,
                        id: note.id
                    }
					$http.put('/api/v1/notes/edit/' + note.id, $scope.updatedNote)
                        .then(function(response){
                            $scope.notes.body = response.data.body;
                            $scope.noteSuccess = true;
                            setTimeout(function (){
                                $scope.$apply(function(){
                                    $scope.noteSuccess = false;
                                });
                            }, 2000);
                        }).catch((err) => {
                            console.log('error', err)
                        })
				}
			});
		};

		$scope.deleteNote = function(index) {
			var note_id = $scope.notes[index].id;
			$http.delete('/api/v1/notes/delete/' + note_id).then(function(response) {
                $scope.deleted = true;
                $scope.deletedMessage = response.data[0];
                $scope.notes.splice(index, 1);
                setTimeout(function (){
                    $scope.$apply(function(){
                        $scope.deleted = false;
                    });
                }, 2000);
			});
		}

		$scope.toggleAccordion = function(index){
			angular.forEach($scope.notes, function(note){
				note.active = 0;
				$('.triggerSlide-'.index).click(function(){
					$('.noteSlide-'.index).slideDown(500);
				});
			});
			$scope.notes[index].active = 1;
		};

		$scope.showMoreNotes = function() {
			$scope.noteCount = $scope.notes.length - 1;

			if ($scope.notes.length >= 10) {
				$scope.limit = $scope.limit + 3;
			}
			else {
				$scope.limit = $scope.limit + 1;
			}

			if ($scope.limit >= $scope.noteCount){
				$scope.hideButton = true;
			}
		}
	}
})();

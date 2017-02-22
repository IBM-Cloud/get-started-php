//Submit data when enter key is pressed
  $('#user_name').keydown(function(e) {
    var name = $('#user_name').val();
      if (e.which == 13 && name.length > 0) { //catch Enter key
        //POST request to API to create a new visitor entry in the database
          $.ajax({
    method: "POST",
    url: "./api/visitors",
    contentType: "application/json",
    data: JSON.stringify({name: name })
  })
          .done(function(data) {
              $('#response').html(data);
              $('#nameInput').hide();
              getNames();
          });
      }
  });

  //Retreive all the visitors from the database
  function getNames(){
    $.get("./api/visitors")
        .done(function(data) {
            if(data.length > 0) {
              $('#databaseNames').html("Database contents: " + JSON.stringify(data));
            }
        });
    }

    //Call getNames on page load.
    getNames();

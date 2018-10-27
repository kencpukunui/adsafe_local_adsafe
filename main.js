window.onload = init;

function init() {
    var flag = false;
 
    if(flag === false) {
        document.getElementById("id_conid").selectedIndex = 0;
        $('#id_locid').load('getter.php?conid=' + $('#id_conid').val());
        flag = true;
    }

    // When a select is changed, look for the students based on the department id
    // and display on the dropdown students select
    $('#id_conid').click(function() {
        $('#id_locid').load('getter.php?conid=' + $('#id_conid').val()); 
        flag = true;
    });
}


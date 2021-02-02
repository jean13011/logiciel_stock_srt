$(document).ready(function(){
    $(".add-row").click(function(){
        let reference = $("#reference").val();
        let ref = " <td class='text-center'><input type='text' value='' id='ref' placeholder=\"Référence\"></td>"
        let name = " <td class='text-center'><input type='text' id='name' placeholder=\"Nom\"></td> "
        let quantity = " <td class='text-center w-25'><input type='number' id='name' min=0></td> "
        let society = " <td class='text-center'><input type='text' id='name' placeholder=\"Société\"></td> "

        let markup = "<tr><td><input type='checkbox' class='form-check-input' name='record' id='box'></td> " + "<td class='text-center'>" + reference + "</td>" + name + quantity + society + "</tr>";
        $("table tbody").append(markup);
    });

    // Find and remove selected table rows
    $(".delete-row").click(function(){
        $("table tbody").find('input[name="record"]').each(function(){
            if($(this).is(":checked")){
                $(this).parents("tr").remove();
            }
        });
    });
});



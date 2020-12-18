var number = parseInt(document.querySelector("#quantity").textContent);

/**
 * this function is call if the button + is clicked , it add 1 at the current number and send it to modifyQuantity for the treatment with ajax
 */
function Plus()
{
    number+=1

    modifyQuantity(); 
}

/**
 * this function is call if the button - is clicked , it remove 1 at the current number and send it to modifyQuantity for the treatment with ajax
 */
function Moin()
{
    if(number === 0 )
    {
        return;
    }

    number-=1

    modifyQuantity();
} 

/**
 * depending on Plus() or Moin() it add or delete 1 and send it to ajax and remplace the new value in the DB
 */
function modifyQuantity()
{
    var id = parseInt(document.querySelector("#id").textContent);

    let ajax = new XMLHttpRequest();
    let form = new FormData();

    form.append("id", id);
    form.append("number", number);

    ajax.onreadystatechange = function()
    {
        if (ajax.readystate === 3) 
        {
            //loader
        }

        if(ajax.readyState === 4  && ajax.status === 200)
        {
            let result = JSON.parse(ajax.responseText); 
            
            document.querySelector("#quantity").innerHTML = result.resultat.quantity;          
        }
        console.log(ajax);
    }

    ajax.open("POST", "https://127.0.0.1:8000/modifyQuantity");
    ajax.send(form);
}
function delProduct()
{
    var del = document.querySelectorAll("#suppr");
    var conf = confirm('Voulez vous supprimer ce produit ?');
    if(conf == false)
    {
        return
    } else 
    {
       alert("Produit supprimé");
    }
} 
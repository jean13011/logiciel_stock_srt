import React from 'react';

const Table = () => {

    function Button(props){
        return <button type={"button"} onClick={props.onClick}>Ajouter une ligne</button>
    }

    function AddRow(){
        const handleClick = ()  => {
            return(
                <table className="table table-bordered bg-white" id="test">
                    <thead>
                        <tr>
                            <th className="text-center" scope="col">Référence</th>
                            <th className="text-center" scope="col">Nom</th>
                            <th className="text-center" scope="col">Quantité</th>
                            <th className="text-center" scope="col">Société</th>
                        </tr>
                    </thead>
                    <tbody>
                        <th className="text-center"><input type="text" id="ref" placeholder="Référence"/></th>
                        <td className="text-center"><input type="text" id="name" placeholder="Nom"/></td>
                        <td className="text-center"><input type="number" id="quantity" min="0"/></td>
                        <td className="text-center"><input type="text" id="society" placeholder="Société"/></td>
                    </tbody>
                </table>
            )
        }
        return <Button onClick={handleClick} />
    }

    return(
        <div>
           {/* <AddRow />*/}
        </div>
    )
};

export default Table;

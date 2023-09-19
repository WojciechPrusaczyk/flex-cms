import React from "react";
const SectionsListItem = (props) => {
    const id = "section-" + props.id;
    let isActive = null;
    if (props.active === true)
    {
        isActive = <span className="active" aria-label="tak">
                <span className="tooltip">Aktywny</span>
        </span>;
    }
    else {
        isActive = <span className="inactive" aria-label="nie">
                <span className="tooltip">Nie aktywny</span>
        </span>;
    }

    return <tr id={ id } className="sections-list-table-tbody-item">
        <td className="sections-list-table-tbody-item-name"><h2>{props.name}</h2></td>
        <td className="sections-list-table-tbody-item-lastEditedBy"><span>{props.lastEditedBy}</span></td>
        <td className="sections-list-table-tbody-item-changeOrder"><img className="sections-list-table-tbody-item-changeOrder-icon" src="/build/icons/dashboard/changeOrder.svg" alt="zmień kolejność" /></td>
        <td className="sections-list-table-tbody-item-active">{isActive}</td>
        <td className="sections-list-table-tbody-item-edit">
            <a href={`${window.location}/edit?id=${props.id}`} alt="Edytuj">
                <img className="sections-list-table-tbody-item-edit-icon" src="/build/icons/dashboard/edit.svg" alt="edytuj" />
            </a>
        </td>
        <td className="sections-list-table-tbody-item-delete">
            <a href={`${window.location}/delete/${props.id}`} alt="Usuń"
               onClick={ (e) =>  {
                   e.preventDefault();
                   props.deleteHandler(props.id);
               } }>
                <img className="sections-list-table-tbody-item-delete-icon" src="/build/icons/dashboard/trashCan.svg" alt="usuń" />
            </a>
        </td>
    </tr>
}

export default SectionsListItem;
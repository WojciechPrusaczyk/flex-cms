import React, {Component} from "react";

const prevIcon = <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20px" height="20px" stroke="currentColor"
                      stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      className="feather feather-chevron-left">
    <polyline points="15 18 9 12 15 6"></polyline>
</svg>;

const nextIcon = <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20px" height="20px" stroke="currentColor"
                      stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      className="feather feather-chevron-right">
    <polyline points="9 18 15 12 9 6"></polyline>
</svg>;

const PageControls = (props) => {

    return <div id="page-controls" className="page-controls">

        <span className="page-controls-numeration"> {props.currentPage} / {props.pagesCount} </span>

        <label htmlFor="page-number" className="page-controls-pageNumber-label">Przejdź do strony</label>
        <input id="page-number" className="page-controls-pageNumber" type="number" min="1" max={props.pagesCount} onChange={ () => { props.changePage(document.getElementById("page-number").value) } } />

        <button id="next-page" className="page-controls-prevPage" type="button" onClick={props.prevPage} >{prevIcon}</button>
        <button id="next-page" className="page-controls-nextPage" type="button" onClick={props.nextPage} >{nextIcon}</button>

        <label htmlFor="elements-per-page" className="page-controls-elementsPerPage-label">Elementy na stronie</label>
        <input id="elements-per-page" className="page-controls-elementsPerPage" type="text" onChange={ () => { props.changeQuantityPerPage(document.getElementById("elements-per-page").value) } } />
    </div>
}

export default PageControls;
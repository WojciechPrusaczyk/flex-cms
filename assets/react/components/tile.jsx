import ReactDOM from "react-dom/client";
import React, {Component} from "react";
import {render} from "react-dom";

const tile = (props) => {
  return <a className={`tile${(props.isActive)?"":" disabled"}`} href={props.href}>
      <img className="tile-image" src={`/build/icons/dashboard/index/${props.icon}`} alt={`${props.name} icon`}/>
      <h2 className="tile-title">{props.name}</h2>
  </a>
}

export default tile;
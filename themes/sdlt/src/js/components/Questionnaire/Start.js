// @flow

import React, {Component} from "react";
import type {User} from "../../types/User";
import LogoImage from "../../../img/Logo.svg";
import LightButton from "../Button/LightButton";

type Props = {
  title: string,
  subtitle: string,
  keyInformation: string,
  user: User
};

class Start extends Component<Props> {

  render() {
    return (
      <div className="Start">
        <div className="start-form">
          <div className="info-box">
            <div className="key-info-title">Key Information:</div>
            <div className="key-info"
                 dangerouslySetInnerHTML={{
                   __html: this.props.keyInformation
                 }}
            />
            <div className='user-info'>
              <div className="info-line">
                <b>Your Name: </b>
                <span>{this.props.user.name}</span>
              </div>
              <div className="info-line">
                <b>Your Role: </b>
                <span>{this.props.user.role}</span>
              </div>
              <div className="info-line">
                <b>Email Address: </b>
                <span>{this.props.user.email}</span>
              </div>
            </div>
            <div className="actions">
              <LightButton title="START" onClick={() => {
                window.location.href = "/#/questionnaire/submission/asdf";
              }} />
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default Start;

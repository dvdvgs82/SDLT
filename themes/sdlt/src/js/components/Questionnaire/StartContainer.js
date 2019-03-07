// @flow

import React, {Component} from "react";
import {connect} from "react-redux";
import type {RootState} from "../../store/RootState";
import {Dispatch} from "redux";
import Start from "./Start";
import {loadQuestionnaireStartState} from "../../actions/questionnarie";
import type {QuestionnaireStartState} from "../../store/QuestionnaireState";
import Header from "../Header/Header";
import Footer from "../Footer/Footer";

const mapStateToProps = (state: RootState) => {
  return {
    startState: state.questionnaireState.startState,
  };
};

const mapDispatchToProps = (dispatch: Dispatch, props: *) => {
  return {
    dispatchLoadQuestionnaireAction(questionnaireID: string) {
      dispatch(loadQuestionnaireStartState(questionnaireID));
    },
  };
};

type ownProps = {
  questionnaireID: string
};

type reduxProps = {
  startState: QuestionnaireStartState,
  dispatchLoadQuestionnaireAction: (id: string) => void
};

type Props = ownProps & reduxProps;

class StartContainer extends Component<Props> {

  componentDidMount() {
    const {questionnaireID, dispatchLoadQuestionnaireAction} = {...this.props};
    dispatchLoadQuestionnaireAction(questionnaireID);
  }

  render() {
    const {title, subtitle, keyInformation, user} = {...this.props.startState};

    if(!user) {
      return null;
    }

    return (
      <div className="StartContainer">
        <Header title={title} subtitle={subtitle} />

        <Start title={title}
               subtitle={subtitle}
               keyInformation={keyInformation}
               user={user}
        />

        <Footer/>
      </div>
    );
  }
}

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(StartContainer);

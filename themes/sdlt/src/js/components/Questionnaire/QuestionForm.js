// @flow
import React, {Component} from "react";
import type {AnswerAction, AnswerInput, Question} from "../../types/Questionnaire";
import _ from "lodash";
import {Field, Form, Formik, FormikBag} from "formik";
import LightButton from "../Button/LightButton";
import DarkButton from "../Button/DarkButton";

type Props = {
  question: Question,
  handleFormSubmit: (formik: FormikBag, values: Object) => void,
  handleActionClick: (action: AnswerAction) => void
};

class QuestionForm extends Component<Props> {

  render() {
    const {question} = {...this.props};

    return (
      <div className="QuestionForm">
        <div className="heading">
          {question.heading}
        </div>
        <div className="description">
          {question.description}
        </div>

        {this.renderActions(question)}
        {this.renderInputsForm(question)}
      </div>
    );
  }

  renderActions(question: Question) {
    const {handleActionClick} = {...this.props};
    const actions: Array<AnswerAction> = _.get(question, "actions", null);
    if (!actions) {
      return null;
    }

    // Render message of the chosen action
    let message = null;
    const chosenAction = actions.find((action) => action.isChose);
    if (chosenAction && chosenAction.message) {
      message = (
        <div className="message">
          <b>Message:</b>
          <div dangerouslySetInnerHTML={{__html: chosenAction.message}}/>
        </div>
      );
    }

    return (
      <div>
        <div className="actions">
          {actions.map((action, index) => {
            switch (index) {
              case 0:
                return <DarkButton title={action.label} key={action.id} classes={["mr-3"]} onClick={() => {
                  handleActionClick(action);
                }}/>;
              default:
                return <LightButton title={action.label} key={action.id} classes={["mr-3"]} onClick={() => {
                  handleActionClick(action);
                }}/>;
            }
          })}
        </div>
        {message}
      </div>
    );
  }

  renderInputsForm(question: Question) {
    const inputs: Array<AnswerInput> = _.get(question, "inputs", null);
    if (!inputs) {
      return null;
    }

    const initialValues = {};
    inputs.forEach((input) => {
      initialValues[input.id] = input.data || "";
    });

    return <Formik
      initialValues={initialValues}
      validate={values => {
        let errors = {};
        inputs.forEach((input: AnswerInput) => {
          const {id, type, required, label, minLength} = {...input};
          const value = _.get(values, id, null);

          // Required
          if (required && !value) {
            errors[id] = `- Please enter a value for ${label}`;
            return;
          }

          // Min Length
          if (minLength > 0 && value && value.length < minLength) {
            errors[id] = `- Please enter a value with at least ${minLength} characters for ${label}`;
            return;
          }

          // Email
          if (type === "email" &&
            !/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i.test(value)) {
            errors[id] = "- Invalid email address";
            return;
          }

          // TODO: Date validation
        });

        return errors;
      }}
      onSubmit={(values, formik) => {
        this.props.handleFormSubmit(formik, values);
      }}
    >
      {({isSubmitting, errors}) => (
        <Form>
          <table>
            <tbody>
            {inputs.map((input) => {
              const {id, type, required, label} = {...input};
              const hasError = Boolean(_.get(errors, id, null));
              const classes = [];
              if (hasError) {
                classes.push("error");
              }

              if (["text", "email", "date"].includes(type)) {
                return (
                  <tr key={id}>
                    <td className="label"><label>{label}</label></td>
                    <td>
                      <Field type={type} name={id} className={classes.join(" ")}/>
                      {hasError && <i className="fas fa-exclamation-circle text-danger ml-1"/>}
                    </td>
                  </tr>
                );
              }

              if (type === "textarea") {
                return (
                  <tr key={id}>
                    <td><label>{label}</label></td>
                    <td>
                      <Field name={id}>
                        {({field}) => {
                          return <textarea {...field} className={classes.join(" ")}/>;
                        }}
                      </Field>
                      {hasError && <i className="fas fa-exclamation-circle text-danger ml-1"/>}
                    </td>
                  </tr>
                );
              }
              return null;
            })}
            <tr>
              <td/>
              <td>
                <DarkButton title="Continue" disabled={isSubmitting} />
              </td>
            </tr>
            <tr>
              <td/>
              <td className="text-danger">
                {errors && _.keys(errors).length > 0 && (
                  <div>
                    Whoops!
                    {_.keys(errors).map((key) => {
                      return (
                        <div className="text-error" key={key}>{errors[key]}</div>
                      );
                    })}
                  </div>
                )}
              </td>
            </tr>
            </tbody>
          </table>
        </Form>
      )}
    </Formik>;
  }
}

export default QuestionForm;
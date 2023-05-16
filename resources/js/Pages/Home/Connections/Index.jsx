import React, { useState } from 'react';
import { Formik } from 'formik';

export default function Connection(props) {
    return (
        <div>
            <h1 className="my-3 fw-bold">Account Details</h1>
            <Formik
                initialValues={{ email: '', password: '', token: '' }}
                validate={values => {
                    const errors = {};
                    if (!values.email) {
                        errors.email = 'Required';
                    } else if (
                        !/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i.test(values.email)
                    ) {
                        errors.email = 'Invalid email address';
                    }
                    return errors;
                }}
                onSubmit={(values, { setSubmitting }) => {
                    setTimeout(() => {
                        alert(JSON.stringify(values, null, 2));
                        setSubmitting(false);
                    }, 400);
                }}
            >
                {({
                      values,
                      errors,
                      touched,
                      handleChange,
                      handleBlur,
                      handleSubmit,
                      isSubmitting,
                      /* and other goodies */
                  }) => (
                    <form onSubmit={handleSubmit}>
                        <div className="row">
                            <div className="col-sm-4 mb-3">
                                <label htmlFor="email" className="form-label fw-bold">Username:</label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    className="form-control"
                                    onChange={handleChange}
                                    onBlur={handleBlur}
                                    value={values.email}
                                />
                                {errors.email && touched.email && errors.email}
                            </div>

                            <div className="col-sm-4 mb-3">
                                <label htmlFor="password" className="form-label fw-bold">Password:</label>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    className="form-control"
                                    onChange={handleChange}
                                    onBlur={handleBlur}
                                    value={values.password}
                                />
                                {errors.password && touched.password && errors.password}
                            </div>

                            <div className="col-sm-4 mb-3">
                                <label htmlFor="token" className="form-label fw-bold">Token:</label>
                                <input
                                    id="token"
                                    type="text"
                                    name="token"
                                    className="form-control"
                                    onChange={handleChange}
                                    onBlur={handleBlur}
                                    value={values.token}
                                />
                                {errors.token && touched.token && errors.token}
                            </div>
                        </div>

                        <div className="row justify-content-end">
                            <div className="col-sm-3">
                                <button className="btn btn-primary w-50 float-end" type="submit" disabled={isSubmitting}>
                                    Connect
                                </button>
                            </div>
                        </div>

                    </form>
                )}
            </Formik>
        </div>
    )
}

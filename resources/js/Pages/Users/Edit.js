import { Inertia } from '@inertiajs/inertia'
import React, {useState} from 'react'
import {InertiaLink} from "@inertiajs/inertia-react";
import Authenticated from "@/Layouts/Authenticated";

export default function Edit(props) {
  const {user} = props
  const [values, setValues] = useState({
    name: user.name,
    email: user.email,
  })

  function handleChange(e) {
    const key = e.target.id;
    const value = e.target.value
    setValues(values => ({
      ...values,
      [key]: value,
    }))
  }

  function handleSubmit(e) {
    e.preventDefault()
    Inertia.put(route('users.update', user.id), values)
  }

  return (
    <Authenticated
      auth={props.auth}
      errors={props.errors}
      header={
        <h2 className="font-semibold text-xl text-gray-800 leading-tight">
          <InertiaLink href={route('users.index')}>Users</InertiaLink> | Edit
        </h2>
      }
    >
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 bg-white border-b border-gray-200">
              <div className="mt-10 sm:mt-0">
                <div className="md:grid md:grid-cols-3 md:gap-6">
                  <div className="md:col-span-1">
                    <div className="px-4 sm:px-0">
                      <h3 className="text-lg font-medium leading-6 text-gray-900">Personal Information</h3>
                      <p className="mt-1 text-sm text-gray-600">Use a permanent address where you can receive mail.</p>
                    </div>
                  </div>
                  <div className="mt-5 md:mt-0 md:col-span-2">
                    <form onSubmit={handleSubmit}>
                      <div className="shadow overflow-hidden sm:rounded-md">
                        <div className="px-4 py-5 bg-white sm:p-6">
                          <div className="grid grid-cols-6 gap-6">
                            <div className="col-span-6 sm:col-span-3">
                              <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                Name
                              </label>
                              <input
                                type="text"
                                name="first-name"
                                id="name"
                                autoComplete="name"
                                value={values.name}
                                onChange={handleChange}
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                              />
                            </div>

                            {/*<div className="col-span-6 sm:col-span-3">*/}
                            {/*  <label htmlFor="last-name" className="block text-sm font-medium text-gray-700">*/}
                            {/*    Last name*/}
                            {/*  </label>*/}
                            {/*  <input*/}
                            {/*    type="text"*/}
                            {/*    name="last-name"*/}
                            {/*    id="last-name"*/}
                            {/*    autoComplete="family-name"*/}
                            {/*    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"*/}
                            {/*  />*/}
                            {/*</div>*/}

                            <div className="col-span-6 sm:col-span-4">
                              <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                                Email address
                              </label>
                              <input
                                type="text"
                                name="email"
                                id="email"
                                autoComplete="email"
                                value={values.email}
                                onChange={handleChange}
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                              />
                            </div>

                            <div className="col-span-6 sm:col-span-3">
                              <label htmlFor="country" className="block text-sm font-medium text-gray-700">
                                Country / Region
                              </label>
                              <select
                                id="country"
                                name="country"
                                autoComplete="country"
                                className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                              >
                                <option>United States</option>
                                <option>Canada</option>
                                <option>Mexico</option>
                              </select>
                            </div>

                            <div className="col-span-6">
                              <label htmlFor="street-address" className="block text-sm font-medium text-gray-700">
                                Street address
                              </label>
                              <input
                                type="text"
                                name="street-address"
                                id="street-address"
                                autoComplete="street-address"
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                              />
                            </div>

                            <div className="col-span-6 sm:col-span-6 lg:col-span-2">
                              <label htmlFor="city" className="block text-sm font-medium text-gray-700">
                                City
                              </label>
                              <input
                                type="text"
                                name="city"
                                id="city"
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                              />
                            </div>

                            <div className="col-span-6 sm:col-span-3 lg:col-span-2">
                              <label htmlFor="state" className="block text-sm font-medium text-gray-700">
                                State / Province
                              </label>
                              <input
                                type="text"
                                name="state"
                                id="state"
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                              />
                            </div>

                            <div className="col-span-6 sm:col-span-3 lg:col-span-2">
                              <label htmlFor="postal-code" className="block text-sm font-medium text-gray-700">
                                ZIP / Postal
                              </label>
                              <input
                                type="text"
                                name="postal-code"
                                id="postal-code"
                                autoComplete="postal-code"
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                              />
                            </div>
                          </div>
                        </div>
                        <div className="px-4 py-3 bg-gray-50 text-right sm:px-6">
                          <button
                            type="submit"
                            className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                          >
                            Save
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Authenticated>
  )
}

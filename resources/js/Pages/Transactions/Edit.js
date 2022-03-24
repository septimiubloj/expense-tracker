import {Inertia} from '@inertiajs/inertia'
import React, {useState} from 'react'
import {InertiaLink, usePage} from "@inertiajs/inertia-react";
import Authenticated from "@/Layouts/Authenticated";

export default function Edit(props) {
  const {transaction, accounts, books, categories} = usePage().props
  const [values, setValues] = useState({
    title: transaction.title,
    description: transaction.description,
    value: transaction.value,
    category_id: transaction.category_id,
    account_id: transaction.account_id,
    book_id: transaction.book_id,
  })

  function handleChange(e) {
    const key = e.target.id;
    const value = e.target.value
    setValues(values => ({
      ...values,
      [key]: value,
    }))
  }

  function handleDelete() {
    if (confirm('Are you sure you want to delete this transaction?')) {
      Inertia.delete(route('transactions.destroy', transaction.id));
    }
  }

  function handleSubmit(e) {
    e.preventDefault()
    Inertia.put(route('transactions.update', transaction.id), values)
  }

  return (
    <Authenticated
      auth={props.auth}
      errors={props.errors}
      header={
        <h2 className="font-semibold text-xl text-gray-800 leading-tight">
          <InertiaLink href={route('transactions.index')}>Transactions</InertiaLink> | Log
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
                      <h3 className="text-lg font-medium leading-6 text-gray-900">Transaction Details</h3>
                      <p className="mt-1 text-sm text-gray-600">Try to keep the description short.</p>
                    </div>
                  </div>
                  <div className="mt-5 md:mt-0 md:col-span-2">
                    <form onSubmit={handleSubmit}>
                      <div className="shadow overflow-hidden sm:rounded-md">
                        <div className="px-4 py-5 bg-white sm:p-6">
                          <div className="grid grid-cols-6 gap-6">

                            <div className="col-span-6 sm:col-span-4">
                              <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                Title
                              </label>
                              <input
                                type="text"
                                name="title"
                                id="title"
                                autoComplete="title"
                                placeholder="Transaction title"
                                value={values.title}
                                onChange={handleChange}
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                              />
                            </div>

                            <div className="col-span-6 sm:col-span-4">
                              <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                Description
                              </label>
                              <input
                                type="text"
                                name="description"
                                id="description"
                                autoComplete="description"
                                placeholder="Transaction description"
                                value={values.description}
                                onChange={handleChange}
                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                              />
                            </div>

                            <div className="col-span-6 sm:col-span-4">
                              <label htmlFor="currentAmount" className="block text-sm font-medium text-gray-700">
                                Value
                              </label>
                              <div className="mt-1 relative rounded-md shadow-sm">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                  <span className="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input
                                  type="text"
                                  name="value"
                                  id="value"
                                  autoComplete="Transaction value"
                                  placeholder="0.00"
                                  value={values.value}
                                  onChange={handleChange}
                                  className="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 shadow-sm sm:text-sm border-gray-300 rounded-md"
                                />
                              </div>
                            </div>

                            <div className="col-span-6 sm:col-span-4">
                              <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                Account
                              </label>
                              <select
                                id="account_id"
                                name="account_id"
                                autoComplete="Account"
                                placeholder="Account"
                                value={values.account_id}
                                onChange={handleChange}
                                required
                                className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                              >
                                <option value="" disabled>Select account</option>
                                {accounts.map(({id, title}) => (
                                  <option key={id} value={id}>
                                    {title}
                                  </option>
                                ))}
                              </select>
                            </div>

                            <div className="col-span-6 sm:col-span-4">
                              <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                Category
                              </label>
                              <select
                                id="category_id"
                                name="category_id"
                                autoComplete="Category"
                                placeholder="Category"
                                value={values.category_id}
                                onChange={handleChange}
                                required
                                className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                              >
                                <option value="" disabled>Select category</option>
                                {categories.map(({id, title}) => (
                                  <option key={id} value={id}>
                                    {title}
                                  </option>
                                ))}
                              </select>
                            </div>

                            <div className="col-span-6 sm:col-span-4">
                              <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                Book
                              </label>
                              <select
                                id="book_id"
                                name="book_id"
                                autoComplete="Book"
                                placeholder="Book"
                                value={values.book_id}
                                onChange={handleChange}
                                required
                                className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                              >
                                <option value="" disabled>Select book</option>
                                {books.map(({id, title}) => (
                                  <option key={id} value={id}>
                                    {title}
                                  </option>
                                ))}
                              </select>
                            </div>

                          </div>
                        </div>
                        <div className="px-4 py-3 bg-gray-50 text-right sm:px-6">
                          <InertiaLink
                            href={route('transactions.index')}
                            className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-400 hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 mr-3"
                          >
                            Cancel
                          </InertiaLink>
                          <button
                            type="button"
                            onClick={handleDelete}
                            className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mr-3"
                          >
                            Delete
                          </button>
                          <button
                            type="submit"
                            className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                          >
                            Update
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

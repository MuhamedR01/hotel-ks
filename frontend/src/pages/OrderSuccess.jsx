
import { useEffect, useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'

function OrderSuccess() {
  const location = useLocation()
  const navigate = useNavigate()
  const [orderData, setOrderData] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Check if we have order data in location state
    if (location.state?.orderNumber && location.state?.orderData) {
      setOrderData({
        orderNumber: location.state.orderNumber,
        ...location.state.orderData
      })
      setLoading(false)
    } else {
      // If no order data, redirect to home immediately
      navigate('/', { replace: true })
    }
  }, [location.state, navigate])

  // Show loading while checking for order data
  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Duke ngarkuar...</p>
        </div>
      </div>
    )
  }

  // If no order data after loading, show nothing (will redirect)
  if (!orderData) {
    return null
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <div className="max-w-3xl mx-auto">
          {/* Success Message */}
          <div className="bg-white rounded-lg shadow-md p-8 mb-6">
            <div className="text-center mb-8">
              <div className="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <h1 className="text-3xl font-bold text-gray-900 mb-2">Porosia u Krye me Sukses!</h1>
              <p className="text-gray-600">Faleminderit për porosinë tuaj</p>
            </div>

            {/* Order Number */}
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">Numri i Porosisë</p>
                  <p className="text-2xl font-bold text-blue-600">#{orderData.orderNumber}</p>
                </div>
                <div className="text-right">
                  <p className="text-sm text-gray-600">Data</p>
                  <p className="font-semibold text-gray-900">{new Date().toLocaleDateString('sq-AL')}</p>
                </div>
              </div>
            </div>

            {/* Order Details */}
            <div className="border-t border-gray-200 pt-6">
              <h2 className="text-xl font-bold text-gray-900 mb-4">Detajet e Porosisë</h2>
              
              {/* Customer Info */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                  <p className="text-sm text-gray-600">Emri</p>
                  <p className="font-semibold text-gray-900">{orderData.name}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Email</p>
                  <p className="font-semibold text-gray-900">{orderData.email}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Telefoni</p>
                  <p className="font-semibold text-gray-900">{orderData.phone}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Qyteti</p>
                  <p className="font-semibold text-gray-900">{orderData.city}</p>
                </div>
              </div>

              <div className="mb-6">
                <p className="text-sm text-gray-600">Adresa e Dërgimit</p>
                <p className="font-semibold text-gray-900">{orderData.address}</p>
              </div>

              {/* Order Items */}
              <div className="border-t border-gray-200 pt-4">
                <h3 className="font-bold text-gray-900 mb-3">Produktet</h3>
                <div className="space-y-3">
                  {orderData.items?.map((item, index) => (
                    <div key={index} className="flex justify-between items-center py-2">
                      <div className="flex-1">
                        <p className="font-medium text-gray-900">{item.name}</p>
                        <p className="text-sm text-gray-500">Sasia: {item.quantity}</p>
                      </div>
                      <p className="font-semibold text-gray-900">{(item.price * item.quantity).toFixed(2)}€</p>
                    </div>
                  ))}
                </div>
              </div>

              {/* Order Summary */}
              <div className="border-t border-gray-200 mt-4 pt-4 space-y-2">
                <div className="flex justify-between text-gray-600">
                  <span>Nëntotal:</span>
                  <span>{orderData.subtotal?.toFixed(2)}€</span>
                </div>
                <div className="flex justify-between text-gray-600">
                  <span>Dërgesa:</span>
                  <span>{orderData.shipping?.toFixed(2)}€</span>
                </div>
                <div className="flex justify-between text-gray-600">
                  <span>Taksë:</span>
                  <span>{orderData.tax?.toFixed(2)}€</span>
                </div>
                <div className="flex justify-between font-bold text-lg text-gray-900 pt-2 border-t border-gray-200">
                  <span>Totali:</span>
                  <span>{orderData.total?.toFixed(2)}€</span>
                </div>
              </div>
            </div>

            {/* Payment Info */}
            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
              <div className="flex items-start">
                <svg className="w-5 h-5 text-yellow-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                </svg>
                <div>
                  <p className="font-semibold text-yellow-800">Pagesë në Dorëzim</p>
                  <p className="text-sm text-yellow-700 mt-1">
                    Porosia juaj do të dërgohet së shpejti. Pagesa do të bëhet në momentin e dorëzimit.
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Home Button */}
          <div className="text-center">
            <Link
              to="/"
              className="inline-flex items-center justify-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-8 rounded-lg transition duration-200 shadow-md hover:shadow-lg"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              <span>Kthehu në Faqen Kryesore</span>
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}

export default OrderSuccess

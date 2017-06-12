import Promise from 'promise'
import Dialog from 'bootstrap3-dialog'
import 'bootstrap3-dialog/dist/css/bootstrap-dialog.css'

function confirm(message, options) {
  return new Promise((resolve, reject) => {
    Dialog.confirm($.extend({}, options, {
      message: makeMessage(message),
      callback: (result) => {
        result ? resolve(result) : reject(result)
      }
    }))
  })
}

function makeMessage(message) {
  if (!$.isArray(message)) {
    message = [message];
  }
  return message.join('')
}

export default {
  confirm
}

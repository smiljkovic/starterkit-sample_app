
import { isEmpty, isEmptyArray, isNullOrUndefined } from './helpers'

// 👉 Required Validator
export const requiredValidator = value => {
  if (isNullOrUndefined(value) || isEmptyArray(value) || value === false)
    return 'This field is required'
  
  return !!String(value).trim().length || 'This field is required'
}

// 👉 Email Validator
export const emailValidator = value => {
  if (isEmpty(value))
    return true
  const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
  if (Array.isArray(value))
    return value.every(val => re.test(String(val))) || 'The Email field must be a valid email'
  
  return re.test(String(value)) || 'The Email field must be a valid email'
}

// 👉 Password Validator
export const passwordValidator = password => {
  const regExp = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}/
  const validPassword = regExp.test(password)
  
  return validPassword || 'Field must contain at least one uppercase, lowercase, special character and digit with min 8 chars'
}

// 👉 Confirm Password Validator
export const confirmedValidator = (value, target) => value === target || 'The Confirm Password field confirmation does not match'

// 👉 Between Validator
export const betweenValidator = (value, min, max) => {
  const valueAsNumber = Number(value)
  
  return (Number(min) <= valueAsNumber && Number(max) >= valueAsNumber) || `Enter number between ${min} and ${max}`
}

// 👉 Integer Validator
export const integerValidator = value => {
  if (isEmpty(value))
    return true
  if (Array.isArray(value))
    return value.every(val => /^-?[0-9]+$/.test(String(val))) || 'This field must be an integer'
  
  return /^-?[0-9]+$/.test(String(value)) || 'This field must be an integer'
}

// 👉 Regex Validator
export const regexValidator = (value, regex) => {
  if (isEmpty(value))
    return true
  let regeX = regex
  if (typeof regeX === 'string')
    regeX = new RegExp(regeX)
  if (Array.isArray(value))
    return value.every(val => regexValidator(val, regeX))
  
  return regeX.test(String(value)) || 'The Regex field format is invalid'
}

// 👉 Alpha Validator
export const alphaValidator = value => {
  if (isEmpty(value))
    return true
  
  return /^[A-Z]*$/i.test(String(value)) || 'The Alpha field may only contain alphabetic characters'
}

// 👉 URL Validator
export const urlValidator = value => {
  if (isEmpty(value))
    return true
  const re = /^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/
  
  return re.test(String(value)) || 'URL is invalid'
}

// 👉 Length Validator
export const lengthValidator = (value, length) => {
  if (isEmpty(value))
    return true
  
  return String(value).length === length || `The Min Character field must be at least ${length} characters`
}

// 👉 Alpha-dash Validator
export const alphaDashValidator = value => {
  if (isEmpty(value))
    return true
  const valueAsString = String(value)
  
  return /^[0-9A-Z_-]*$/i.test(valueAsString) || 'All Character are not valid'
}



//👉 - Nikola - validate credit/debit cards



const MILLENNIUM = 1000
const DEFAULT_CODE_LENGTH = 3

const CARDS = [
 /*FIXME -  {
    name: 'Banescard',
    bins: /^(603182)[0-9]{10,12}/,
    codeLength: 3,
  },
  {
    name: 'Maxxvan',
    bins: /^(603182)[0-9]{10,12}/,
    codeLength: 3,
  },
  {
    name: 'Cabal',
    bins: /^(604324|604330|604337|604203|604338)[0-9]{10,12}/,
    codeLength: 3,
  },
  {
    name: 'GoodCard',
    bins: /^(606387|605680|605674|603574)[0-9]{10,12}/,
    codeLength: 3,
  },
  {
    name: 'Elo',
    bins: /^(401178|401179|431274|438935|451416|457393|457631|457632|504175|627780|636297|636368|(506699|5067[0-6]\d|50677[0-8])|(50900\d|5090[1-9]\d|509[1-9]\d{2})|65003[1-3]|(65003[5-9]|65004\d|65005[0-1])|(65040[5-9]|6504[1-3]\d)|(65048[5-9]|65049\d|6505[0-2]\d|65053[0-8])|(65054[1-9]|6505[5-8]\d|65059[0-8])|(65070\d|65071[0-8])|65072[0-7]|(6509[0-9])|(65165[2-9]|6516[6-7]\d)|(65500\d|65501\d)|(65502[1-9]|6550[3-4]\d|65505[0-8]))[0-9]{10,12}/,
    codeLength: 3,
  },
  {
    name: 'Diners',
    bins: /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/,
    codeLength: 3,
  },
  {
    name: 'Discover',
    bins: /^6(?:011|5[0-9]{2}|4[4-9][0-9]{1}|(22(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[01][0-9]|92[0-5]$)[0-9]{10}$))[0-9]{12}$/,
    codeLength: 4,
  },
  {
    name: 'Amex',
    bins: /^3[47][0-9]{13}$/,
    codeLength: 4,
  },
  {
    name: 'Aura',
    bins: /^50[0-9]{14,17}$/,
    codeLength: 3,
  },*/
  {
    name: 'Mastercard',
    bins: /^(603136|603689|608619|606200|603326|605919|608783|607998|603690|604891|603600|603134|608718|603680|608710|604998)|(5[1-5][0-9]{14}|2221[0-9]{12}|222[2-9][0-9]{12}|22[3-9][0-9]{13}|2[3-6][0-9]{14}|27[01][0-9]{13}|2720[0-9]{12})$/,
    codeLength: 3,
  },
  {
    name: 'Visa',
    bins: /^4[0-9]{12}(?:[0-9]{3})?$/,
    codeLength: 3,
  },
 /*FIXME -     {
 name: 'Hipercard',
    bins: /^(38[0-9]{17}|60[0-9]{14})$/,
    codeLength: 3,
  },
  {
    name: 'JCB',
    bins: /^(?:2131|1800|35\d{3})\d{11}$/,
    codeLength: 3,
  },*/
]

export const getCreditCardNameByNumber = number => {
  //return findCreditCardObjectByNumber(number).name || 'Credit card is invalid!'
  return findCreditCardObjectByNumber(number).name || 'unknown'
}

export const isSecurityCodeValid = (creditCardNumber, securityCode) => {
  const numberLength = getCreditCardCodeLengthByNumber(creditCardNumber)
  
  return new RegExp(`^[0-9]{${numberLength}}$`).test(securityCode)
}

export const isExpirationDateValid = (date) => {
  const pieces = date.split("/");

  return (
    isValidMonth(pieces[0]) &&
    isValidYear(pieces[1]) &&
    isFutureOrPresentDate(pieces[0], pieces[1])
  )
}

export const isValid = (number, options = {}) => {
  const { cards } = options
  const rawNumber = removeNonNumbersCaracteres(number)

  if (hasSomeInvalidDigit(number) || !hasCorrectLength(rawNumber)) {
    return false
  }

  const sum = sumNumber(rawNumber)
  const chk = checkSum(sum) && validateCardsWhenRequired(number, cards)
  if (chk)
    return true
  else
    return 'Credit card is invalid!'
  
  
}

function validateCardsWhenRequired(number, cards) {
  return !cards || !cards.length || validateCards(number, cards)
}

function validateCards(number, cards) {
  return (
    areCardsSupported(cards) &&
    cards
      .map(c => c.toLowerCase())
      .includes(getCreditCardNameByNumber(number).toLowerCase())
  )
}

function hasCorrectLength(number) {
  return number && number.length <= 19
}

function removeNonNumbersCaracteres(number) {
  return number.replace(/\D/g, '')
}

function hasSomeInvalidDigit(creditcardNumber) {
  const invalidDigits = new RegExp('[^0-9- ]')
  
  return invalidDigits.test(creditcardNumber)
}

function checkSum(sum) {
  return sum > 0 && sum % 10 === 0
}

function areCardsSupported(passedCards) {
  const supportedCards = CARDS.map(c => c.name.toLowerCase())
  
  return passedCards.every(c => supportedCards.includes(c.toLowerCase()))
}

function findCreditCardObjectByNumber(number) {
  if (!number) return {}
  const numberOnly = number.replace(/[^\d]/g, '')
  
  return CARDS.find(card => card.bins.test(numberOnly) && card) || {}
}

function getCreditCardCodeLengthByNumber(number) {
  return findCreditCardObjectByNumber(number).codeLength || DEFAULT_CODE_LENGTH
}

function isValidMonth(month) {
  return !isNaN(month) && month >= 1 && month <= 12
}

function isValidYear(year) {
  return !isNaN(year) && isValidFullYear(formatFullYear(year))
}

function formatFullYear(year) {
  if (year.length === 2) return dateRange(year)

  return year.length === 4 ? year : 0
}

function dateRange(increaseYear = 0) {
  const year = parseInt(increaseYear)
  const today = new Date()
  
  return Math.floor(today.getFullYear() / MILLENNIUM) * MILLENNIUM + year
}

function isValidFullYear(year) {
  return year >= dateRange() && year <= dateRange(MILLENNIUM)
}

function isFutureOrPresentDate(month, year) {
  const fullYear = formatFullYear(year)
  const currentDate = new Date()
  const expirationDate = new Date()

  currentDate.setFullYear(currentDate.getFullYear(), currentDate.getMonth(), 1)
  expirationDate.setFullYear(fullYear, month - 1, 1)

  return currentDate <= expirationDate
}

function sumNumber(number) {
  const computed = [0, 2, 4, 6, 8, 1, 3, 5, 7, 9]
  let sum = 0
  let digit = 0
  let i = number.length
  let even = true

  while (i--) {
    digit = Number(number[i])
    sum += (even = !even) ? computed[digit] : digit
  }

  return sum
}

require 'selenium-webdriver'

describe 'WordPress login' do

  before(:each) do
    @driver = Selenium::WebDriver.for :firefox
  end

  after(:each) do
    @driver.quit
  end

  it 'succeeded' do
    @driver.get 'http://single.damien.home/wp-admin'
    @driver.find_element(id: 'user_login').send_keys('damien')
    @driver.find_element(id: 'user_pass').send_keys('1234')
    @driver.find_element(id: 'wp-submit').submit
  end

end
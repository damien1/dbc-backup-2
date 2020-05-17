require 'selenium-webdriver'

describe 'WordPress login' do

  before(:each) do
    @driver = Selenium::WebDriver.for :firefox
  end

  after(:each) do
    @driver.quit
  end

  it 'succeeded' do
    @driver.get 'http://test.url.home/wp-admin'
    @driver.find_element(id: 'user_login').send_keys('tester')
    @driver.find_element(id: 'user_pass').send_keys('password')
    @driver.find_element(id: 'wp-submit').submit
  end

end

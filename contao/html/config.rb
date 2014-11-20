require 'compass/import-once/activate'

# Autoprefixer
# @see https://github.com/postcss/autoprefixer#compass
require 'autoprefixer-rails'

on_stylesheet_saved do |file|
  css = File.read(file)
  File.open(file, 'w') do |io|
    io << AutoprefixerRails.process(css)
  end
end

# Project settings
http_path = "system/modules/dc-general/html/"
css_dir = "css"
sass_dir = "sass"
images_dir = "images"
javascripts_dir = "js"
output_style = :compressed
relative_assets = true
line_comments = false

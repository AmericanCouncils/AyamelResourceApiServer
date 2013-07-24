#!/usr/bin/env ruby

require 'set'
require 'rest_client'
require 'json'

if ARGV.size != 3
    puts
    puts "This script adds YouTube videos to an Ayamel server."
    puts
    puts "Supply three arguments: "
    puts " * A path to a page containing YouTube URLs"
    puts " * The hostname of an Ayamel server"
    puts " * An access key"
    puts
    exit 1
end

fileName, hostName, key = ARGV
baseUrl = "http://#{hostName}/api/v1"

videos = Set.new
f = open(fileName)
f.each do |line|
    if line =~ /watch\?v=([\w-]+)/
        videos.add($1)
    end
end
f.close()

videos.each do |vid|
    begin
        begin
            r1 = RestClient.get("#{baseUrl}/resources/scan", {
                :params => {
                    :_key => key,
                    :_format => 'json',
                    :uri => "youtube://#{vid}"
                },
                :accept => :json
            })

            r2 = RestClient.post("#{baseUrl}/resources",
                JSON.parse(r1)["resource"].to_json,
                :params => {
                    :_key => key,
                    :_format => 'json'
                },
                :content_type => :json,
                :accept => :json
            )
            contentUploadUrl = JSON.parse(r2)["contentUploadUrl"]

            RestClient.post(contentUploadUrl,
                {:uri => "youtube://#{vid}"}.to_json,
                :params => {
                    :_key => key,
                    :_format => 'json'
                },
                :content_type => :json,
                :accept => :json
            )
        rescue Exception => e
            puts "Exception"
            puts e.inspect
            throw e
        end
    end
end

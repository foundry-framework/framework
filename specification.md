#Foundry Framework Specification
This document outlines the specification for the foundry framework and what it must or should support.

It has been constructed from research into several other frameworks and attempts to take the best of these mixed with experience and combine them to formulate a scalable and adaptable framework.

##Introduction / Monolog
Many frameworks for development exist. They have all been designed with the goal of solving common problems which those programmers have faced. The goal of the frameworks is to help reduce the development time in producing a product.

The Foundry Framework is not a replacement to these. The frameworks that exist are good and there is sufficient development of and around them. Foundry Framework is more about building on top of these frameworks to support a more declarative methodology to application development. It also aims to further reduce the code written and ensure developers don’t repeat themselves.

It has the intention of being opinionated as this results in good standards, reasonable better code, a quicker development time, and a simpler learning curve.  

As programming languages advance, the common goal that will be experienced is eventually programs that only need to be told what to do, not how to do it. Frameworks are attempting to do this however they still require the developer to know the framework. (This is not an attempted to remove knowledge of the framework, just improve how the parts of the frameworks work with one another so that the how it does things does not need to be the concern of the developer).

A good example of this is Laravel’s Eloquent module: You still need to detail much about how it should work and more could be done to make it further declarative.

So the goal with the Foundry Framework is to further decouple concepts about web development and further abstract it away from what these frameworks have done, and use the frameworks to implement this abstraction. Foundry Framework should at best maintain a declarative approach and truly aim towards not having to redefine logic or write code to solve common problems.
 
Example: Once you’ve told the Foundry Framework how its data is structured, you should never have to write anything further to modify or control the interchange of that data. How the data is stored is not the programmers concern, he just needs to tell the framework what data he is wanting to use. The framework should worry about the rest.

Another Example: Object Relational Mapping exists already as a common standard across frameworks. However in implementations like Eloquent, it is not intelligent enough and relies on the developer to specifically tell it how it should connect relationships and also how it should store that data. As an example, you cannot tell Eloquent, fetch me all the Users and also include their Profile data WITHOUT doing multiple calls to the data. Also Eloquent is too connected to the data level and there should be a further level of abstraction so that ANY data storage mechanism could be used to persist the Model to a data source.

Note: It is NOT the intention of the Foundry Framework to re-invent the wheel. Most of the issues it aims to solve already exist, but not necessarily within a single framework.

The concept is more closely related to Drones. You don’t tell a Drone how to fly, just that it should. Most frameworks today are too concerned with the “how to fly”, which is OK and that is why the foundry framework will use them and NOT re-invent the wheel.

MVC development is good but has led to too much code and too much of a burden to carry on a single controller. There are also varying views on this. Further, too much logic gets built into the Controllers and a lack of Services or a core is developed. The Foundry Framework attempts to move business logic into Services and rather leave Controllers lean and handling types of requests. 

This will also open up the door to so that a single request could involved multiple services (which is already the case and why the traditional MVC model will naturally bloat).

The Foundry Framework is more tightly bound to Requests and Responses than to a single MVC.

##Definitions
 - 'should' means the item should be supported if possible and practical.
 - 'must' means the item is required and must be supported.
 - 'request' means any type of call and is not limited to an HTTP request. Even a call to a service should be regarded as a request.
 - 'response' means any type of returned variable or object and is not limited to an HTTP response. Even a response from a call to a service should be regarded as a response.
 - 'HTTP request' or 'HTTP response' specifically means a request or response via HTTP.

#Specifications
Each of the below is broken into parts or concepts for easy discussion and understanding.

##Goals
1.	The end developer should write less code.
1.	The framework should be declarative.
1.	Data structure should be defined centrally and the system must be intelligent enough to know what it should do when ask to interact (fetch, save, update, delete) data.
1.	Validation should be defined once and be extensible for each specific use case for a given action.
1.	Requests (Incoming and Internal "inter-service/plugin") should be wrapped with some type of Data Request Object which will keep the state, validate, and or transform the request.
1.	ORM’s and Models must be further abstracted to ensure that the developer does not have to tell the system how to get the data. The developer should be telling the system to fetch or store the data (how, where, what, when, should not be the concern of the developer).
1.	Event driven should be at the core of the framework so that the application can be extended in a multitude of ways.
1.	An endpoint can be mapped to one or several parts of the system. An endpoint only represents a request. This would make it possible to request 1 part of a response, such as a part of a page.
1.	It should be possible to return multiple API responses for a single API request. This would mean an API call would be the same whether you are requesting a page or a part of a system. A page is just a collection of responses.

##Plugins
 - The system must work on plugins. 
 - Each plugin is effectively it’s own mini App.
 - Each plugin provides Services which all other programs must talk through to engage with that plugin.
 - Dependency injection should use Contracts rather than requiring a specific class object. This will result in a strong standard around communications and expected methods.
 - Each plugin should be independently testable and have units tests written for calling its services. 
 - Example resources:
    - [https://nwidart.com/laravel-modules/v3/introduction](https://nwidart.com/laravel-modules/v3/introduction)

##API Core
 - The system must have a central application core.
 - Each of the plugins extend the core by adding their services.
 - Calling a service and a method would then be something like `$app('api')->serviceName($params…)`, or `$app('serviceAlias')->methodName($params...)`. 
    - This will allow for the API to be exposed via REST or other API development practices without having to specifically write specific API Controllers.
    - This will also allow for easy API adapters to be developed which are just simply wrappers to call a service method on the server. This concept is similar to [https://guide.meteor.com/methods.html](Meteor's Methods).
    - This is also a similar concept to how getCandy is built. See below resource link.
 - Whilst using the API to call services, it does not mean plugins cannot use classes of other plugins directly but this should be avoided. 
 - Example Resources:
    - **Get Candy:** [https://getcandy.io/](https://getcandy.io/) and [https://github.com/getcandy/candy-api](https://github.com/getcandy/candy-api). A commerce API framework is a great example of the ideal api calling concept and a central API 

##Models / ORM
 - The system should use Doctrine which is a proven and versatile ORM workable across multiple data sources including SQL and NoSQL databases.
 - The Models must be simple and effective and should represent an object and the methods related to the object.
 - The Models must not be concerned with persistence or saving the data. A separate part of the system, like a "Store" should keep this responsibility. This will ensure their performance is optimal but the object is still practical.
 - Repositories must be used to communicated with data sources and return Models. 

##HTML and View Components
Before listing the specifications it is worth indicating and changing the viewpoint on what HTML and frontend rendering should become.

Traditionally, MVC relies on the Controller receiving the request and feeding it through to a View. However, building front end applications, the approach is different where the View is directing the rendering of the page.

Effectively, the structure of the system should be in 2 parts:
 - API core with services.
 - View UI which implements those services into some sort of front end.
 
So in essence, a "website" is just an implementation of view components, which in turn use services to collect needed data and then render out a response. The same concept exists for a backend "admin" section, or any other HTML application or experience.

So the goal with View Components should be to provide building blocks of HTML and View Objects, which can be easily created and put together to render a website or any HTML output.

Therefor the Foundry Framework must support a View driven HTML rendering mechanism, and not follow the traditional MVC approach.

In fact, Controllers should be for broad "type" of request/response handling. Such as a Controller for the website, a controller for the backend, and a controller for REST calls. There must not be any need for a ProductsController to handle product creation. There must be a JobsService which implements a product creation method, and the View Component implements an event or binding to call the ProductService to perform the action.

Think of View Components as front-end code (on the server or the client) and Services as backend code (on the server) and then Service Adapters are communication adapters for communicating client to and from the server. 

**Specifications**:
 - The system must implement a View First concept and not use the traditional MVC methodology.



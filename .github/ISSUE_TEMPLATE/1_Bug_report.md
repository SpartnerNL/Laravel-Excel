--- 
body: 
  - 
    attributes: 
      description: "Currently 3.1.x"
      label: "Is the bug applicable and reproducable to the latest version of the package and hasn't it been reported before?"
      options: 
        - 
          label: "Yes, it's still reproducable"
          required: true
    id: terms
    type: checkboxes
  - 
    attributes: 
      description: "For example: 3.1.30"
      label: "What version of Laravel Excel are you using?"
    type: input
    validations: 
      required: true
  - 
    attributes: 
      description: "For example: 7.1.10"
      label: "What version of Laravel are you using?"
    type: input
    validations: 
      required: true
  - 
    attributes: 
      description: "For example: 8.1.0"
      label: "What version of PHP are you using?"
    type: input
    validations: 
      required: true
  - 
    attributes: 
      description: "Describe the problem you're seeing, Please be short, but concise."
      label: "Describe your issue"
    type: textarea
    validations: 
      required: true
  - 
    attributes: 
      description: "Describe the problem you're seeing, Please be short, but concise."
      label: "Describe your issue"
    type: textarea
    validations: 
      required: true
  - 
    attributes: 
      description: "Please provide easy-to-reproduce steps (repository, simple code example, failing unit test). Please don't paste your entire code, but create a reproducable scenario that can be tested using a simple User model in a blank Laravel installation."
      label: "How can the issue be reproduced?"
    type: textarea
    validations: 
      required: true
  - 
    attributes: 
      description: "Please describe what the expected outcome should be. Any suggestions to what is wrong?"
      label: "What should be the expected behaviour?"
    type: textarea
    validations: 
      required: true
description: "Report a general package issue. Filling in the issue template is mandatory, issues without it will be closed. Please ensure your Laravel-Excel version is still supported (Currently ^3.1)"
labels: 
  - bug
name: "Bug Report"
title: "[Bug]: "

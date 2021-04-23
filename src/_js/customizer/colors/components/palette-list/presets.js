const presets = [ {
  uid: `preset1`,
  quote: "This color makes me feel at home",
  image: 'https://images.unsplash.com/flagged/photo-1557533046-154fc97b729f?ixid=MnwxMjA3fDB8MXxzZWFyY2h8MXx8dHJvcGljYWx8fDB8fHx8MTYxOTE2NjM3MQ&ixlib=rb-1.2.1&dpr=1&auto=format&fit=crop&w=120&h=200&q=60',
  config: [
    {
      "uid": "color_group_0",
      "sources": [
        {"uid": "color_0", "label": "Color 1", "value": "#1d7e70"},
        {"uid": "color_1", "label": "Color 2", "value": "#efdfd2"},
        {"uid": "color_2", "label": "Color 3", "value": "#3addf3"}
      ]
    }
  ]
}, {
  uid: `preset2`,
  quote: "I prefer living in color",
  image: 'https://images.unsplash.com/photo-1549772262-a128a0224ea5?ixid=MnwxMjA3fDB8MXxzZWFyY2h8NHx8Y2l0eSUyMGxpZ2h0c3x8MHx8fHwxNjE5MTY5ODI0&ixlib=rb-1.2.1&dpr=1&auto=format&fit=crop&w=120&h=200&q=60',
  config: [ {
    "uid": "color_group_0",
    "sources": [
      {"uid": "color_0", "label": "Color 1", "value": "#38578e"},
      {"uid": "color_1", "label": "Color 2", "value": "#78a5ce"}
    ]
  },
  {
    "uid": "color_group_1",
    "sources": [
      {"uid": "color_0", "label": "Color 1", "value": "#6c45c3"}
    ]
  } ]
}, {
  uid: `preset3`,
  quote: "Life is about using the whole box of crayons",
  image: 'https://images.unsplash.com/photo-1564107628966-daff03746bee?ixid=MnwxMjA3fDB8MXxzZWFyY2h8MjV8fGRlc2VydHxlbnwwfHx8fDE2MTkxNjA1ODM&ixlib=rb-1.2.1&dpr=1&auto=format&fit=crop&w=120&h=200&q=60',
  config: [
    {
      "uid": "color_group_0",
      "sources": [
        {"uid": "color_0", "label": "Color 1", "value": "#e1995d"}
      ]
    },
  ]
}, {
  uid: `preset4`,
  quote: "Let me, O let me bathe my soul in colors.",
  image: 'https://images.unsplash.com/photo-1515935811948-4f4d2eabbd4e?ixid=MnwxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNjE5MTg5MjAx&ixlib=rb-1.2.1&dpr=1&auto=format&fit=crop&w=120&h=200&q=60',
  config: [
    {
      "uid": "color_group_0",
      "sources": [
        {"uid": "color_0", "label": "Color 1", "value": "#fde169"},
        {"uid": "color_1", "label": "Color 2", "value": "#920060"}
      ]
    }
  ]
} ]

export default presets;
